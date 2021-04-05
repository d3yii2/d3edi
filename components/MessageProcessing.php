<?php


namespace d3yii2\d3edi\components;


use d3system\commands\D3CommandController;
use d3yii2\d3edi\logic\MessageLogic;
use EDI\Reader;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;

class MessageProcessing extends Component
{
    /**
     * @var string server IP or domain name
     */
    public $server;

    /**
     * @var int
     */
    public $port = 22;
    /**
     * @var string
     */
    public $privateKeyPassword;

    /**
     * @var string
     */
    public $privateKeyPath;

    /**
     * @var string
     */
    public $userName;

    /**
     * @var string
     */
    public $password;
    /**
     * @var SFTP
     */
    public $sftp;

    /**
     * @var bool
     */
    public $deleteOnFtpServerFiles = false;

    /** @var string remote outbond directory */
    public $remoteOutbondDir;

    /** @var string inbound new directory path */
    public $localUploadInboundNewDir;

    /** @var string inbound processed directory path */
    public $localUploadInboundProcessedDir;

    /** @var D3CommandController */
    public $commandController;
    /**
     * @throws Exception
     */
    public function connect($command): void
    {
        $this->commandController = $command;
        $this->sftp = new SFTP($this->server,$this->port);

        if($this->privateKeyPath) {
            /** create new RSA key */
            $privateKey = new RSA();

            if($this->privateKeyPassword) {
                $privateKey->setPassword($this->privateKeyPassword);
            }

            /** load the private key */
            $privateKey->loadKey(file_get_contents($this->privateKeyPath));

            /** login via sftp */
            $this->out('Login by private key');
            if (!$this->sftp->login($this->userName, $privateKey)) {
                throw new Exception('sFTP login failed'. VarDumper::dumpAsString($this->sftp->getErrors()));
            }

            return;
        }

        /** login via sftp */
        $this->out('Login by username and password');
        if (!$this->sftp->login($this->userName, $this->password)) {
            throw new Exception('sFTP login failed'. VarDumper::dumpAsString($this->sftp->getErrors()));
        }

    }

    /**
     * Download
     *  - get from FTPS server
     *  - save to directory new
     *  - remove from FTPS server
     *
     * @return mixed
     * @throws Exception
     */
    public function downloadFtp(): void
    {

        $sftp = $this->sftp;
        if(!$sftp->chdir($this->remoteOutbondDir)){
            $this->out('Can not on FTP cd outbound');
            Yii::error('Can not on FTP cd outbound');
            throw new Exception('Can not on FTP cd outbound');
        }
        $this->out('Download files from SFTP');
        FileHelper::createDirectory($this->localUploadInboundNewDir);
        foreach ($sftp->nlist() as $fileName) {
            $this->out($fileName);
            if (strtoupper(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'EDI') {
                $this->out('  -ignore');
                continue;
            }

            $localFile = $this->localUploadInboundNewDir . DIRECTORY_SEPARATOR . $fileName;
            if(file_exists($localFile)){
                continue;
            }
            if (!$sftp->get($fileName, $localFile)) {
                $this->out('  - can not download');
                Yii::error('Can not load Maersk EDI file ' . $fileName);
                continue;
            }
            if($this->deleteOnFtpServerFiles) {
                if (!$sftp->delete($fileName)) {
                    $this->out('  - can not delete');
                    Yii::error('Can not delete Maersk EDI file ' . $fileName);
                }else{
                    $this->out('  - deleted on FTP');
                }
            }
            $this->out('  - downloaded');
        }
    }

    private function out(String $message){
        $this->commandController->out($message);
    }

    public function getNewFiles()
    {
        return FileHelper::findFiles($this->localUploadInboundNewDir, ['only' => ['*.edi','*.EDI']]);
    }

    public function moveFileToProcessedDirectory(string $fileName)
    {
        rename($fileName, $this->getUpladInboundProcessedFilePath($fileName));
    }

    /**
     * load filt to DB
     *  - get file from directory new,
     *  - save to table edi_message,
     *  - move to directory processed
     *
     * @throws \yii\db\Exception
     */
    public function loadEdi(string $compnentName = null): void
    {
        $this->out('Load new files from SFTP to DB');
        $logic = new MessageLogic();
        FileHelper::createDirectory($this->localUploadInboundProcessedDir);
        foreach ($this->getNewFiles() as $fileName) {
            $this->out(' ' . basename($fileName));
            $ediMessage = file_get_contents($fileName);
            foreach(Reader::splitMultiMessage($ediMessage) as $message) {
                if(!$transaction = \Yii::$app->db->beginTransaction()){
                    throw new \yii\db\Exception('Can not init transaction');
                }
                try {
                    $logic->saveIn($message, $this->getUpladInboundProcessedFilePath($fileName),$compnentName);
                    $transaction->commit();
                    $this->out('  - loadded');
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    $this->out('Problem with EDI loading to DB: ' . $e->getMessage());
                    \Yii::error('Problem with EDI loading to DB: ' . $e->getMessage());
                    \Yii::error($e->getTraceAsString());
                }
            }
            $this->moveFileToProcessedDirectory($fileName);
        }
    }

    /**
     * @param string $fileName
     * @return string
     */
    private function getUpladInboundProcessedFilePath(string $fileName): string
    {
        return $this->localUploadInboundProcessedDir . '/' . basename($fileName);
    }

}