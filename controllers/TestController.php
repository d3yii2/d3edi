<?php

namespace d3yii2\d3edi\controllers;

use d3system\commands\D3CommandController;
use d3system\helpers\D3FileHelper;
use d3yii2\d3edi\logic\MessageLogic;
use EDI\Analyser;
use EDI\Analyser2;
use EDI\Interpreter;
use EDI\Mapping\MappingProvider;
use EDI\Parser;
use EDI\Reader;
use yii\console\ExitCode;
use yii\helpers\VarDumper;

class TestController extends D3CommandController
{

    /**
     * default action
     * @param string $fileName
     * @return int
     */
    public function actionIndex(string $fileName): int
    {
        //$file = D3FileHelper::getRuntimeFilePath('edi','MAEU.LVRIXBCT.COPARN.1412605.198506621261398674.edi');
        $file = D3FileHelper::getRuntimeFilePath('edi',$fileName);
        $p = new Parser($file);
        $edi = $p->get();

        $mapping = new MappingProvider('D95B');

        $analyser = new Analyser();
        $segs = $analyser->loadSegmentsXml($mapping->getSegments());
        $svc = $analyser->loadSegmentsXml($mapping->getServiceSegments(3));
        $codes = $analyser->loadCodesXml($mapping->getCodes());


        $interpreter = new Interpreter($mapping->getMessage('coparn'), $segs, $svc);
        $interpreter->directory = 'D95B';
        $interpreter->codes = $codes;

        $groups = $interpreter->prepare($edi);
        D3FileHelper::filePuntContentInRuntime('edi','interpreted_groups.json',json_encode($groups));
        $json = $interpreter->getJson(true);
        D3FileHelper::filePuntContentInRuntime('edi','interpreted_json.json',$json);
        $json = $interpreter->getJsonServiceSegments();
        D3FileHelper::filePuntContentInRuntime('edi','interpreted_servicesegments.json',$json);
        $errors = $interpreter->getErrors();
        D3FileHelper::filePuntContentInRuntime('edi','interpreted_errors.json',json_encode($errors));

        return ExitCode::OK;
    }

    public function actionAnalyser(string $fileName, string $runtimeDir = 'edi'): int
    {
        $file = D3FileHelper::getRuntimeFilePath($runtimeDir,$fileName);

        $parser = new Parser($file);
        $parsed = $parser->get();
        $segments = $parser->getRawSegments();
        $analyser = new Analyser2('D95B',D3FileHelper::getRuntimeDirectoryPath('ediDoc'));
        $result = $analyser->process($parsed, $segments);
        $saveFileName = substr_replace($fileName , 'txt', strrpos($fileName , '.') +1);
        D3FileHelper::filePutContentInRuntime($runtimeDir,$saveFileName,$result);

        return ExitCode::OK;
    }
//yii edi/test/to-csv LVRIXAB000000078.EDI msc BGM RFF DTN EQD
    public function actionToCsv(
        string $fileName,
        string $runtimeDir = 'edi',
        string $before,
        string $start,
        string $end,
        string $after
    ): int
    {
        $file = D3FileHelper::getRuntimeFilePath($runtimeDir,$fileName);

        $reader = new Reader($file);
        //$groups = $reader->readGroups('BGM','RFF','DTN','EQD');
        $groups = $reader->readGroups($before,$start,$end,$after);
        echo VarDumper::dumpAsString($groups);

//        $groups = $reader->readGroups('DTM','EQD','FTX','CNT');
//        echo VarDumper::dumpAsString($groups);
        return ExitCode::OK;
    }

    public function actionLoad(string $fileName)
    {
        $message = D3FileHelper::fileGetContentFromRuntime('edi',$fileName);
        $ml = new MessageLogic();
        $ml->saveIn($message);
    }

}

