<?php
/*******************************************************************************/
/* AgataAPI
/* API for report generation from Web PHP scripts
/* by Pablo Dall'Oglio - 2001 - 2006
/*    Jamiel Spezia - 2006 - 2006
/*******************************************************************************/
class AgataAPI
{
    function AgataAPI()
    {
        # Define the Agata's Path
        define('AGATA_PATH', substr(__FILE__,0,-25));
        define('OS', strtoupper(substr(PHP_OS, 0, 3)));
        
        if (OS == 'WIN')
        {
            define("bar", '\\');
            setlocale(LC_ALL, 'POSIX');
            define ("cut", ';');
            if (is_dir('C:\\temp'))
            {
                define("temp", 'C:\\temp');
            }
            else if (is_dir('C:\\windows\\temp'))
            {
                define("temp", 'C:\\windows\\temp');
            }
            else
            {
                define("temp", 'c:\\winnt\\temp');
            }
        }
        else
        {
            define("bar", '/');
            setlocale(LC_ALL, 'english');
            define ("cut", ':');
            define("temp", '/tmp');
        }
        
        define('isGui', false);
        define('BREAKLN', "<br>");
        
        if (!is_dir(AGATA_PATH))
        {
            echo "<b>Error</b> : Wrong Path: " . AGATA_PATH;
        }
        else
        {
            //chdir(AGATA_PATH);
            ini_set('include_path', '.' . cut . '/usr/local/lib/php'. cut .
                    AGATA_PATH . cut . AGATA_PATH . '/classes');
            
            include_once AGATA_PATH . "/include/util.inc";
            include_once AGATA_PATH . "/classes/core/AgataCompatibility.php";
            include_once AGATA_PATH . "/classes/core/AgataConnection.php";
            include_once AGATA_PATH . "/classes/core/AgataQuery.php";
            include_once AGATA_PATH . "/classes/core/AgataError.php";
            include_once AGATA_PATH . "/classes/core/AgataDataSet.php";
            include_once AGATA_PATH . "/classes/core/Project.php";
            include_once AGATA_PATH . "/classes/core/CoreReport.php";
            include_once AGATA_PATH . "/classes/core/Layout.php";
            include_once AGATA_PATH . "/classes/core/AgataCore.php";
            include_once AGATA_PATH . "/classes/core/AgataConfig.php";
            include_once AGATA_PATH . "/classes/util/Wait.php";
            include_once AGATA_PATH . "/classes/util/Dialog.php";
            include_once AGATA_PATH . "/classes/util/MemoArea.php";
            include_once AGATA_PATH . "/classes/util/Trans.php";
            include_once AGATA_PATH . "/classes/util/XmlArray.php";
            include_once AGATA_PATH . "/classes/util/AgataOO.php";
            include_once AGATA_PATH . "/classes/reports/AgataReport.php";
        }
    }

    # Defining the output file that will be generated
    function setOutputPath($output)
    {
        $output = suggestion_archive_name($output);
        touch($output);

        $this->outputPath= $output;
    }

    # Defining the name of the file which will appear as a suggestion
    # for saving as on the user's browser
    function setDownloadFileName($fileName)
    {
        $this->downloadFileName = $fileName;
    }
    
    # Getting the output file that will be generated
    function getOutputPath()
    {
        return $this->outputPath;
    }

    # Defining the Report file to interpret
    function setReportPath($report)
    {
        $this->reportPath = $report;
    }
    
    # Alternative way to setReportPath
    function setReport($Report)
    {
        $this->Report = $Report;
    }

    # Read the Report
    function getReport()
    {
        if ($this->Report)
        {
            return $this->Report;
        }
        else
        {
            # Reading the Report
            $Report = @CoreReport::OpenReport($this->reportPath);
            if (!$Report['Report'])
            {
                $this->error = 'Cannot read report file.';
                return false;
            }
            return $Report;
        }
    }
    
    # Return the last error
    function getError()
    {
        return $this->error;
    }
    
    # Return the report parameters
    function getParameters()
    {
        # You can set the Database connection this way, too:
        $Project = @Project::ReadProject($this->project);
        if (!$Project)
        {
            $this->error = 'Cannot read project file.';
            return;
        }
        
        # Reading the Report
        $Report = $this->getReport();
        if (!$Report)
        {
            return;
        }
        if ($params = array_keys($Report['Report']['Parameters']))
        {
            foreach ($params as $name)
            {
                $return[] = "\${$name}";
            }
            return $return;
        } 
        return null;
    }
    
    # Choosing the output {html, txt, pdf, csv, sxw}
    function setFormat($format)
    {
        $this->format = $format;
    }

    # Set the report parameters
    function setParameter($parameter, $value)
    {
        $this->parameters[$parameter] = $value;
    }

    function setLanguage($language)
    {
        if (!in_array($language, array('en', 'pt', 'es', 'de', 'fr', 'it', 'se')))
        {
            $language = 'en';
        }
        
        # Set the Interface Language
        Trans::SetLanguage($language);
    }

    # Set the layout
    function setLayout($layout)
    {
        $this->layout = $layout;
    }

    # Set the project name
    function setProject($project)
    {
        $this->project = $project;
    }

    # Set report data. This not need Query and Report set, only data
    function setDataArray($dataArray)
    {
        if (is_null($dataArray))
        {
            $dataArray[0][0] = '';
        }
        $this->dataArray = $dataArray;
    }

    # Set report sub data. This not need Query and Report set, only data
    function setSubDataArray($subDataArray)
    {
        if (is_null($subDataArray))
        {
            $subDataArrayb[0][0] = '';
        }
         $this->subDataArray = $subDataArray;
    }

    # Set image replace
    function setImageReplace($imageName, $imagePath)
    {
        $this->imageReplace[$imageName] = $imagePath;
    }

    function fileDialog()
    {
        switch ($this->format)
        {
            case ('txt'):
                $content_type = 'text/plain';
                break;
            case ('csv'):
                $content_type = 'text/plain';
                break;
            case 'xml':
                $content_type = 'text/xml';
                break;
            case 'pdf':
                $content_type = 'application/pdf';
                break;
            case 'ps':
                $content_type = 'application/postscript';
                break;
            case 'sxw':
                $content_type = 'application/sxw';
                break;
            case 'dia':
                $content_type = 'application/dia';
                break;
        }

        // Set default file name if none specified
        if ( strlen($this->downloadFileName) <= 0 )
        {
            $this->downloadFileName = basename($this->outputPath);
        }
        if ($this->format != 'html')
        {
            header("Content-Length: " . filesize($this->outputPath));
            header("Content-type: $content_type");
            header("Content-Disposition: attachment; filename=\"" . $this->downloadFileName . "\"");
            header ("Cache-Control: cache"); // HTTP/1.1 
            header ("Content-Transfer-Encoding: binary");
        }
        readfile($this->outputPath);
        $this->removeOutputFile();
    }

    function removeOutputFile()
    {
        @unlink(RemoveExtension($this->getOutputPath()) . '.pdf');
        @unlink(RemoveExtension($this->getOutputPath()) . '.sxw');
    }
    
    function generateReport()
    {
        # You can set the Database connection this way, too:
        $Project = @Project::ReadProject($this->project);
        if (!$Project)
        {
            $this->error = 'Cannot read project file.';
            return;
        }
        
        # Reading the Report
        $Report = $this->getReport();
        if (!$Report)
        {
            return;
        }

        if ($this->parameters)
        {
            $header = $Report['Report']['Header']['Body'];
            $footer = $Report['Report']['Footer']['Body'];
            
            foreach ( $this->parameters as $key => $value )
            {
                $header = str_replace($key,$value,$header);
                $footer = str_replace($key,$value,$footer);
            }
            $Report['Report']['Header']['Body'] = $header;
            $Report['Report']['Footer']['Body'] = $footer;
        }
        
        $DataSet = $Report['Report']['DataSet'];
        
        # Process the Query.
        $Query = AgataCore::CreateQuery($Project, $DataSet, $this->parameters);

        if (is_agata_error($Query))
        {
            $this->error = $Query->GetError();
            return;
        }
        else
        {
            $params[0] = $Project;
            $params[1] = null;
            $params[2] = $this->outputPath;
            $params[3] = $Query;
            $params[4] = $Report;
            $params[6] = $this->layout;
            
            $myreport = AgataCore::CreateReport($this->format, $params);
            $myreport->Process();
        }
        return true;
    }


    function generateDocument()
    {
        $this->format = 'pdf';
        
        # You can set the Database connection this way, too:
        $Project = @Project::ReadProject($this->project);
        if (!$Project)
        {
            $this->error = 'Cannot read project file.';
            return;
        }
        
        # Reading the Report
        $Report = $this->getReport();
        if (!$Report)
        {
            return;
        }
        
        $DataSet = $Report['Report']['DataSet'];
        
        # Process the Query.
        $Query = AgataCore::CreateQuery($Project, $DataSet, $this->parameters);
        if (is_agata_error($Query))
        {
            $this->error = $Query->GetError();
            return;
        }
        else
        {
            $params[0] = $Project;
            $params[1] = null;
            $params[2] = $this->outputPath;
            $params[3] = $Query;
            $params[4] = $Report;
            $params[6] = $this->parameters;
            
            $obj = AgataCore::CreateMergedDocument($params, 'Pdf');
            $obj->Generate();
        }
        return true;
    }

    function SetUserStyle($style)
    {
        $this->user_style = $style;
    }

    function parseOpenOffice($source)
    {
        $this->format = 'sxw';

        if ($this->dataArray)
        {
            #Process the data.
            $Query = AgataCore::CreateQueryArray($this->dataArray);
        }
        else
        {
            # You can set the Database connection this way, too:
            $Project = @Project::ReadProject($this->project);
            if (!$Project)
            {
                $this->error = 'Cannot read project file.';
                return;
            }
            
            # Reading the Report
            $Report = $this->getReport();
            if (!$Report)
            {
                return;
            }
            
            $DataSet = $Report['Report']['DataSet'];
            # Process the Query.
            $Query = AgataCore::CreateQuery($Project, $DataSet, $this->parameters);
        }

        if (is_agata_error($Query))
        {
            $this->error = $Query->GetError();
            return;
        }
        else
        {
            $params[0] = $Project;
            $params[1] = null;
            $params[2] = $this->outputPath;
            $params[3] = $Query;
            $params[4] = $Report;
            $params[6] = $this->parameters;
            
            if (!$source)
            {
                $Report = $this->getReport();
                $source = $Report['Report']['OpenOffice']['Source'];
            }
            $config = AgataOO::GetConfig($source);
            $obj = AgataCore::ParseOpenOffice($params, 'Sxw', $config['engine']);

            if ($this->subDataArray)
            {
                $obj->SetSubDataArray($this->subDataArray);
            }
            if ($this->user_style)
            {
                $obj->SetUserStyle($this->user_style);
            }
            if ($this->imageReplace)
            {
                foreach ($this->imageReplace as $imageName => $imagePath)
                {
                    $obj->SetImageReplace($imageName, $imagePath);
                }
            }

            $obj->Generate($source, $this->outputPath);
        }
        return true;
    }

    function openOffice2pdf($file)
    {
        $this->format = 'pdf';
        $this->outputPath = RemoveExtension($file) . '.pdf';
        $config = AgataConfig::ReadConfig();

        // If a @1 is found, replace it with filename
        $cmn = str_replace('@1', $file, $config['app']['OpenofficeToPdf']);
        // Two strings being equal means no @1 found. If so, add $file to the
        // end of it (this is for backwards compatibility).
        if ( $cmn == $config['app']['OpenofficeToPdf'] )
        {
                $cmn =  $config['app']['OpenofficeToPdf'] . ' '  . $file;
        }

        system($cmn, $return);
        if ($return == 0)
        {
            return true;
        }
        $this->error = 'Error converting sxw to pdf. Command return code: ' . $return . '.';
        return false;
    }

    function generateLabel()
    {
        $this->format = 'pdf';
        
        # You can set the Database connection this way, too:
        $Project = @Project::ReadProject($this->project);
        if (!$Project)
        {
            $this->error = 'Cannot read project file.';
            return;
        }
        
        # Reading the Report
        $Report = $this->getReport();
        if (!$Report)
        {
            return;
        }
        
        $DataSet = $Report['Report']['DataSet'];
        
        # Process the Query.
        $Query = AgataCore::CreateQuery($Project, $DataSet, $this->parameters);
        if (is_agata_error($Query))
        {
            $this->error = $Query->GetError();
            return;
        }
        else
        {
            $params[0] = $Project;
            $params[1] = null;
            $params[2] = $this->outputPath;
            $params[3] = $Query;
            $params[4] = $Report;
            $params[6] = $this->parameters;
            
            $obj = AgataCore::CreateAddressLabel($params);
            $obj->Generate();
        }
        return true;
    }
}
?>
