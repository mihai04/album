<?php


namespace SearchBundle\Command;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use SearchBundle\Entity\Entities;
use SearchBundle\Entity\Indices;
use SearchBundle\Helper\DatabaseHelperInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SearchCommand
 * @package SearchBundle\Command
 */
class SearchCommand extends ContainerAwareCommand
{
    const COMMAND_HELP = "This command allows you to populate the search_index relation with inputs defined in the
    search_entities relation";

    const COMMAND_DESCRIPTION = "Populate indices for the search function";

    const COMMAND_NAME = 'populate:search:entities';

    /**
     * @var DatabaseHelperInterface
     */
    private $databaseHelper;

    /**
     * SearchCommand constructor.
     *
     * @param DatabaseHelperInterface $databaseHelper
     */
    public function __construct(DatabaseHelperInterface $databaseHelper)
    {
        $this->databaseHelper = $databaseHelper;

        parent::__construct(self::COMMAND_NAME);
    }

    /**
     * Configure Search command.
     */
    protected function configure()
    {
        $this
            ->setDescription(self::COMMAND_DESCRIPTION)
            ->setHelp(self::COMMAND_HELP)
            ->addArgument('tableName', InputArgument::REQUIRED, 'Table name input required.');
    }

    /**
     * Executes search:update:entities command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $outputStyle = new OutputFormatterStyle('red', 'green',  ['bold', 'blink']);
        $output->getFormatter()->setStyle('fire', $outputStyle);

        $output->writeln('Generating indexes...');

        $em = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager');

        $target = $input->getArgument('tableName');

        try {
            $this->databaseHelper->truncate($output, $em, $input->getArgument('tableName'), true);
        } catch (DBALException $e) {
            $output->writeln("<fg=red;options=bold>Error: Failed to truncate table " . $target, $e->getMessage().'</>');
        }

        $terms = $em
            ->getRepository(Entities::class)
            ->getEntities();


        if (empty($terms)) {
            $output->writeln('<fg=yellow;options=bold>Error: There are not entities in your entities table.');
        }
        else {

            /** @var Entities $term */
            foreach ($terms as $term) {
                try {
                    $em
                        ->getRepository(Indices::class)
                        ->generateIndex($term->getEntityName(), $term->getEntityField());

                    $output->writeln('<fg=green;options=bold>Generated indexes for bundle with entity: ' . $term->getEntityName()
                        . ' having field name ' . $term->getEntityField() . '.</>');
                } catch (OptimisticLockException $e) {
                    $output->writeln('<fg=green;options=bold>Generated indexes for bundle with entity: ' . $term->getEntityName()
                        . ' having field ' . $term->getEntityField() . '.</>');
                } catch (ORMException $e) {
                    $output->writeln('<fg=red;options=bold>Failed to indexes for bundle with entity: ' . $term->getEntityName()
                        . ' having field ' . $term->getEntityField() . '.</>');
                }
            }

            $output->writeln('Success');
        }
    }
}