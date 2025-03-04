<?php
/**
 * Copyright (c) 2016 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

namespace Ho\Import\Downloader;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem\Io\Sftp as IoSftp;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class Ftp
 *
 * @todo implement check if file needs to be re-downloaded if not, use the existing file.
 * @package Ho\Import\Downloader
 */
class Sftp extends AbstractDownloader
{

    /**
     * @var IoSftp
     */
    private $sftp;

    /**
     * @var ConsoleOutput
     */
    private $consoleOutput;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $files = [];

    /**
     * @var array
     */
    protected $target;


    /**
     * Ftp constructor.
     *
     * @param DirectoryList $directoryList
     * @param IoSftp         $sftp
     * @param ConsoleOutput $consoleOutput
     */
    public function __construct(
        DirectoryList $directoryList,
        IoSftp $sftp,
        ConsoleOutput $consoleOutput
    ) {
        parent::__construct($directoryList);
        $this->sftp = $sftp;
        $this->consoleOutput = $consoleOutput;
    }


    public function download()
    {
        $this->sftp->open($this->getOptions() + [
            'timeout' => 10,
        ]);

        if (!is_writeable($this->getTargetPath($this->getTarget()))) {
            mkdir($this->getTargetPath($this->getTarget()), 0777, true);
            if (!is_writeable($this->getTargetPath($this->getTarget()))) {
                throw new NotFoundException(__('Target %1 is not writable', [$this->getTarget()]));
            }
        }
        foreach ($this->getFiles() as $sourcePath) {
            $targetPath = $this->getTargetPath($this->getTarget(), basename($sourcePath));

            $this->consoleOutput->writeln((string) __('Downloading %1..', [$sourcePath]));
            $this->sftp->read($sourcePath, $targetPath);
        }

        $this->sftp->close();
    }


    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }


    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }


    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }


    /**
     * @param array $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }


    /**
     * @return array
     */
    public function getTarget()
    {
        return $this->target;
    }


    /**
     * @param array $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

}
