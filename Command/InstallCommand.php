<?php

namespace Stfalcon\Bundle\TinymceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class InstallCommand
 * @package Stfalcon\Bundle\TinymceBundle\Command
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class InstallCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription("Installs tinymce library.")
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Use symlink instead of hard copy.')
            ->setName('stfalcon:tinymce:install')
            ->setHelp(<<<EOT
The <info>stfalcon:tinymce:install</info> command helps you installing the tinymce/tinymce library.
EOT
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem;

        $sourceDir = $this->getSourceDir();
        if (false === file_exists($sourceDir)) {
            $output->writeln(sprintf(
                '<error>Tinymce directory "%s" does not exist. Did you install tinymce/tinymce? '.
                'If you used something other than Composer you need to manually change the path in '.
                '"stfalcon_tinymce.tinymce_src".</error>',
                $sourceDir
            ));

            return;
        }

        $targetDir = $this->getTargetDir();
        try {
            $fs->mkdir($targetDir);
        } catch (IOException $e) {
            $output->writeln(sprintf('<error>Could not create directory %s.</error>', $targetDir));
            return;
        }

        if ($input->getOption('symlink')) {
            try {
                $fs->symlink($sourceDir, $targetDir, true);
            } catch (IOException $e) {
                $output->writeln(sprintf('<error>Symlink creation failed (from %s to %s).</error>', $sourceDir, $targetDir));
                return;
            }
        } else {
            try {
                $fs->mirror($sourceDir, $targetDir);
            } catch (IOException $e) {
                $output->writeln(sprintf('<error>Copy failed (from %s to %s).</error>', $sourceDir, $targetDir));
                return;
            }
        }

        $output->writeln(sprintf('Installed tinymce in <comment>%s</comment>.', $targetDir));
    }

    /**
     * @return string
     */
    protected function getSourceDir()
    {
        return rtrim($this->getContainer()->getParameter('stfalcon_tinymce.tinymce_source_dir'), '/');
    }

    /**
     * @return string
     */
    protected function getTargetDir()
    {
        return rtrim($this->getContainer()->getParameter('stfalcon_tinymce.tinymce_target_dir'), '/');
    }
}
