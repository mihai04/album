<?php


namespace SearchBundle\Command;

use Doctrine\DBAL\DBALException;
use Helper\MySQLDatabaseHelper;
use SearchBundle\Entity\Entities;
use SearchBundle\Entity\SearchIndex;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends ContainerAwareCommand
{
    //https://github.com/Elao/PhpEnums

    const COMMAND_DESCRIPTION = "Populate indexes for the search function";

    const COMMAND_HELP = "This command allows you to populate the search_index relation with inputs defined in the
    search_entities relation";

    const SEARCH_INDEX = 'search_index';

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this
            ->setName('populate:search:entities')
            ->setDescription(self::COMMAND_DESCRIPTION)
            ->setHelp(self::COMMAND_HELP);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Generating indexes....');

        $em = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager');

        // pass argument?

        MySQLDatabaseHelper::truncate($output, $em, self::SEARCH_INDEX, true);

        $terms = $em
            ->getRepository(Entities::class)
            ->getEntities();

        /** @var Entities $searchableTerm */
        foreach($terms as $term) {
            $em
                ->getRepository(SearchIndex::class)
                ->generateIndex($term->getEntityName(), $term->getField());
        }

        $output->writeln('FINISH');
    }
}