<?php
#+-----------------------------------------------------------------+
#| AGATA Report  (http://www.agata.org.br)                         |
#| Copyleft (l) 2004  Solis - Lajeado - RS - Brasil                |
#| Licensed under GPL: http://www.fsf.org for further details      |
#+-----------------------------------------------------------------+
#| Started in  2001, August, 10                                    |
#| Author: Pablo Dall'Oglio (pablo@dalloglio.net)                  |
#+-----------------------------------------------------------------+
#| Agata Report: A Database reporting tool written in PHP-GTK      |
#| This file shows how to use AgataAPI to generate simple reports  |
#+-----------------------------------------------------------------+

# Including the necessary classes and definitions.
include 'start.php';

# Defining the SQL file to interpret
$ReportName  = $file;

if (($type == 'merge') or ($type == 'label'))
    $mimetype = 'pdf';

if (($type == 'lines') or ($type == 'bars'))
    $mimetype = $saida;

# Defining the output file that will be generated:
$Output   = temp . '/output.' . $mimetype;

//# You can set the Databse connection this way, too:
//$Project = Project::ReadProject($connection);

# Reading the Report
$originalReport = $Report = CoreReport::OpenReport($ReportName);

$saveReport = false;

if ($Report)
{
    # What fields to show
    if ($SelectFields)
    {
        $SelectFields = unserialize(ereg_replace("`", "'", $SelectFields));
        $Adjustments = CoreReport::ExtractAdjustments($Report['Report']['DataSet']);
        $newindex = 1;
        foreach ($SelectFields as $SelectField => $value)
        {
            // ajustes
            $index=$SelectField+1;
            $NewAdjustments["column{$newindex}"] = $Adjustments[$index];
            if ($Report['Report']['DataSet']['Groups']['Formulas'])
            {
                foreach ($Report['Report']['DataSet']['Groups']['Formulas'] as $group => $Formula)
                {
                    $Report['Report']['DataSet']['Groups']['Formulas'][$group] = str_replace("($index)", "($newindex)", $Formula);
                }
            }
            $newindex ++;
        }
        $Adjustments = $NewAdjustments;
        $Report['Report']['DataSet']['Query']['Select'] = implode(',', $SelectFields);
        $Report['Report']['DataSet']['Fields'] = $NewAdjustments;
        
        $originalReport['Report']['DataSet']['Query']['AgataWeb']['Select'] = implode(',', $SelectFields);
        $saveReport = true;
    }

    # Aditional constraints
    if ($constraint_entries)
    {
        $constraint_entries   = unserialize($constraint_entries);
        $constraint_fields   = unserialize($constraint_fields);
        $constraint_operators = unserialize($constraint_operators);
        
        foreach ($constraint_entries as $key => $constraint_value)
        {
            if ($constraint_value)
            {
                # remove 'as' from field
                $field = $constraint_fields[$key];
                $add_constraints[] = $field . ' ' . $constraint_operators[$key] .  " '$constraint_value'";
            }
        }
        if ($add_constraints)
        {
            $where     = $Report['Report']['DataSet']['Query']['Where'];
            $new_where = implode(' and ', $add_constraints);
            $Report['Report']['DataSet']['Query']['Where'] = $where ? "$where and $new_where" : $new_where;
            $originalReport['Report']['DataSet']['Query']['AgataWeb']['Where'] = $new_where;
        }
        else
        {
            $originalReport['Report']['DataSet']['Query']['AgataWeb']['Where'] = '';
        }
        $saveReport = true;
    }

    # New Ordering
    if ($ordering)
    {
        $ordering = unserialize($ordering);
        foreach ($ordering as $order)
        {
            if ($order)
            {
                $new_order[] = $order;
            }
        }
        if ($new_order)
        {
            $Report['Report']['DataSet']['Query']['OrderBy'] = implode(',', $new_order);
            $originalReport['Report']['DataSet']['Query']['AgataWeb']['OrderBy'] = implode(',', $new_order);
        }
        $saveReport = true;
    }

    $Report['Report']['Merge']['ReportHeader']                      = $textmerge[1];
    $Report['Report']['Merge']['Details']['Detail1']['GroupHeader'] = $textmerge[2];
    $Report['Report']['Merge']['Details']['Detail1']['Body']        = $textmerge[3];
    $Report['Report']['Merge']['Details']['Detail1']['GroupFooter'] = $textmerge[4];
    $Report['Report']['Merge']['ReportFooter']                      = $textmerge[5];
    $Report['Report']['Label']['Body']                              = $label;

    include_once AGATA_PATH . '/classes/core/AgataAPI.class';
    
    # Instantiate AgataAPI
    $api = new AgataAPI;
    $api->setLanguage('en'); //'en', 'pt', 'es', 'de', 'fr', 'it', 'se'
    $api->setReport($Report);
    $api->setProject($connection);
    $api->setFormat($mimetype); // 'pdf', 'txt', 'xml', 'html', 'csv', 'sxw'
    $api->setOutputPath($Output);
    $api->setLayout($layout);
    
    # Parameters
    //var_dump($Parameters);
    if ($Parameters)
    {
        if (is_string($Parameters)) //serialized from sheet1
        {
            $Parameters = unserialize(ereg_replace("`", "'", $Parameters));
        }
        
        foreach ($Parameters as $Parameter => $value)
        {
            $api->setParameter($Parameter, "$value");
            $Parameter = substr($Parameter,1); // remove "$"
            $originalReport['Report']['Parameters'][$Parameter]['value'] = $value;
        }
    }
    
    if ($type == 'report')
    {
        if ($mimetype == 'oop')
        {
            $api->setFormat('sxw'); // 'pdf', 'txt', 'xml', 'html', 'csv', 'sxw'
            $Output   = temp . '/output.sxw';
            $api->setOutputPath($Output);
            $ok = $api->parseOpenOffice($originalReport['Report']['OpenOffice']['Source']);
            $mimetype = 'sxw';
        }
        else
        {
            $ok = $api->generateReport();
        }
        
    }
    else if ($type == 'merge')
    {
        $ok = $api->generateDocument();
    }
    else if ($type == 'label')
    {
        $ok = $api->generateLabel();
    }
    
    if (!$ok)
    {
        echo $api->getError();
        die;
    }

    if ($saveReport)
    {
        $originalReport['Report']['Properties']['Layout'] = $layout;
        $originalReport['Report']['Properties']['Format'] = $mimetype;
        $originalReport['Report']['DataSet']['DataSource']['Name'] = $connection;
        CoreReport::SaveReport($ReportName, $originalReport);
    }

    /*$DataSet = $Report['Report']['DataSet'];
    # Process the Query.
    $Query = AgataCore::CreateQuery($Project, $DataSet, $Parameters);
    
    if (is_agata_error($Query))
    {
        new Dialog($Query->GetError());
    }
    else
    {
        $params[0] = $Project;
        $params[1] = $agataConfig;
        $params[2] = $Output;
        $params[3] = $Query;
        $params[4] = $Report;
        
        if ($type == 'report')
        {
            $params[6] = $layout;
            $myreport = AgataCore::CreateReport($mimetype, $params);
            $myreport->Process();
        }
        else if ($type == 'merge')
        {
            $params[6] = $Parameters;
            $obj = AgataCore::CreateMergedDocument($params, 'Pdf');
            $obj->Generate();
        }
        else if ($type == 'label')
        {
            $obj = AgataCore::CreateAddressLabel($params);
            $obj->Generate();
        }
        else if ($type=='lines')
        {
            $params[4]['Report']['Graph']['Orientation']    = $orientation;
            $params[4]['Report']['Graph']['Title']          = $title;
            $params[4]['Report']['Graph']['TitleX']         = $titlex;
            $params[4]['Report']['Graph']['TitleY']         = $titley;
            $params[4]['Report']['Graph']['Width']          = $width;
            $params[4]['Report']['Graph']['Height']         = $height;
            $params[4]['Report']['Graph']['Description']    = $description;
            
            if ($saida == 'html')
            {
                $Output = $params[2] = 'web/tmp/output.' . $mimetype;
            }
            foreach ($columns as $column)
            {
                $params[6][] = urldecode($column);
            }
            
            $obj = AgataCore::CreateGraph($params);
            if (isset($legend))
            {
                $obj->Lines(urldecode($legend[0]));
            }
            else
            {
                $obj->Lines();
            }
            if ($saida == 'html')
            {
                header("Location: tmp/output.$mimetype");
                die;
            }
        }
        else if ($type=='bars')
        {
            $params[4]['Report']['Graph']['Orientation']    = $orientation;
            $params[4]['Report']['Graph']['Title']          = $title;
            $params[4]['Report']['Graph']['TitleX']         = $titlex;
            $params[4]['Report']['Graph']['TitleY']         = $titley;
            $params[4]['Report']['Graph']['Width']          = $width;
            $params[4]['Report']['Graph']['Height']         = $height;
            $params[4]['Report']['Graph']['Description']    = $description;
            
            if ($saida == 'html')
            {
                $Output = $params[2] = 'web/tmp/output.' . $mimetype;
            }
            foreach ($columns as $column)
            {
                $params[6][] = urldecode($column);
            }
            
            $obj = AgataCore::CreateGraph($params);
            if (isset($legend))
            {
                $obj->Bars(urldecode($legend[0]));
            }
            else
            {
                $obj->Bars();
            }
            if ($saida == 'html')
            {
                header("Location: tmp/output.$mimetype");
                die;
            }
        }*/
        
        //header("Content-type: application/pdf");
        //header("Content-Disposition: attachment; filename=\"output.pdf\"");
        $download = 'output.' . $mimetype;
        //readfile($Output);
        //echo 'sdf';
        header("Location: download.php?type=$mimetype&download=$download&file=$Output");
        //header("Location: $Output");
    //}
}
else
{
    new Dialog('Cannot read Report File');
}
?>
