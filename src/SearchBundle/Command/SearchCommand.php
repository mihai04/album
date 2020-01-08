<?php


namespace SearchBundle\Command;

use Doctrine\DBAL\DBALException;
use SearchBundle\Entity\Entities;
use SearchBundle\Entity\Indexes;
use SearchBundle\Helper\DatabaseHelper;
use SearchBundle\Helper\DatabaseHelperImpl;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
     * @var DatabaseHelper
     */
    private $databaseHelper;

    /**
     * SearchCommand constructor.
     *
     * @param DatabaseHelper $databaseHelper
     */
    public function __construct(DatabaseHelper $databaseHelper)
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
            ->addArgument('tableName', InputArgument::REQUIRED, 'The table for search indices.');
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
        $output->writeln('Generating indexes...');

        $em = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager');

        $target = $input->getArgument('tableName');

        try {
            $this->databaseHelper->truncate($output, $em, $input->getArgument('tableName'), true);
        } catch (DBALException $e) {
            $output->writeln("Error: Failed to truncate table " . $target, $e->getMessage());
        }

        $terms = $em
            ->getRepository(Entities::class)
            ->getEntities();

        /** @var Entities $term */
        foreach($terms as $term) {
            $em
                ->getRepository(Indexes::class)
                ->generateIndex($term->getEntityName(), $term->getEntityField());
        }

        $output->writeln('Success');
    }
}