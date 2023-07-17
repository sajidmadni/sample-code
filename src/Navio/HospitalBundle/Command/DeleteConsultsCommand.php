<?php
/**
 * User: Nanda Yemparala
 * Date: 2018-12-31
 * Time: 12:37
 */

namespace Navio\HospitalBundle\Command;

use Navio\ConsultBundle\Entity\Consult;
use Navio\HospitalBundle\Entity\HospitalSetting;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteConsultsCommand extends BaseCommand
{

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('navio:consult:deleteConsults');
        $this
            ->addOption('hid',"hid",InputOption::VALUE_REQUIRED,'The Hospital ID')
            ->addOption('type', 't', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Consult status type to delete', [Consult::$STATUS_ABANDONED])
            ->addOption('days',"days",InputOption::VALUE_OPTIONAL,'Number of days to go back');
        $this->setHelp(<<<EOT
            app/console navio:consult:deleteConsults --hid=11
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $hid = $input->getOption('hid');
        $days = $input->getOption('days');
        $em = $this->getContainer()->get('doctrine');
        $hospitalRepo = $em->getRepository('HospitalBundle:Hospital');
        $consultRepo = $em->getRepository('ConsultBundle:Consult');
        if (!$hospitalRepo){
            $output->writeln('Could not create hospital repository object');
            return;
        }
        if (!$consultRepo){
            $output->writeln('Could not create consult repository object');
            return;
        }
        $type = $input->getOption('type');
        if($type == null) {
            $type = [Consult::$STATUS_ABANDONED];
        }
        else if(is_array($type) == false) {
            $type = [$type];
        }
        if (!$hid)
        {
            echo 'hospital not specified'. PHP_EOL;
            return;
        }
        $hospital = $hospitalRepo->find($hid);
        if (!$hospital)
        {
            echo 'unable to find hospital '. $hid. PHP_EOL;
            return;
        }
        else {
            if (!$days){
                $days = $hospital->getSetting(HospitalSetting::$SETTING_DEFAULT_GROUP_CONSULT_CLEAR_DAYS, 7);
            }
            $output->writeln('Clearing consults for '. $hospital->getName() . " clearing consults prior to days :". $days . " with status ".json_encode($type));
            $rows = $consultRepo->clearConsultsForHidWithStatus($hospital, $days, $type);
            $output->writeln("Updated $rows consults");
        }

    }
}