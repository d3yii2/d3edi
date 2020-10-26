<?php

namespace d3yii2\d3edi\controllers;

use d3system\commands\D3CommandController;
use d3system\helpers\D3FileHelper;
use d3yii2\d3edi\logic\MessageLogic;
use EDI\Analyser;
use EDI\Interpreter;
use EDI\Mapping\MappingProvider;
use EDI\Parser;
use yii\console\ExitCode;

class TestController extends D3CommandController
{

    /**
     * default action
     * @return int
     */
    public function actionIndex(): int
    {
        //$file = D3FileHelper::getRuntimeFilePath('edi','MAEU.LVRIXBCT.COPARN.1412605.198506621261398674.edi');
        $file = D3FileHelper::getRuntimeFilePath('edi','out.edi');
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

    public function actionAnalyser(): int
    {
        $file = D3FileHelper::getRuntimeFilePath('edi','MAEU.LVRIXBCT.COPARN.1412605.198506621261398674.edi');
        //$file = D3FileHelper::getRuntimeFilePath('edi','27566.txt');
        $parser = new Parser($file);
        $parsed = $parser->get();
        $segments = $parser->getRawSegments();
        $analyser = new Analyser();
        $mapping = new MappingProvider('D95B');
        $analyser->loadSegmentsXml($mapping->getSegments());
        $analyser->loadMessageXml($mapping->getMessage('coparn'));
        $analyser->loadCodesXml($mapping->getCodes());
        $analyser->directory = 'D95B';
        $result = $analyser->process($parsed, $segments);
        D3FileHelper::filePutContentInRuntime('edi','MAEU.LVRIXBCT.COPARN.1412605.198506621261398674.txt',$result);

        return ExitCode::OK;
    }

    public function actionLoad()
    {
        $message = D3FileHelper::fileGetContentFromRuntime('edi','MAEU.LVRIXBCT.COPARN.1412605.198506621261398674.edi');
        $ml = new MessageLogic();
        $ml->saveIn($message);
    }

}

