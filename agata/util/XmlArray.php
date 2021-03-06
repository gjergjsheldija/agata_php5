<?php
/** vim: set expandtab tabstop=4 shiftwidth=4:
  * +----------------------------------------------------------------------+
  * | PHP Version 4                                                        |
  * +----------------------------------------------------------------------+
  * | Copyright (c) 1997-2002 The PHP Group                                |
  * +----------------------------------------------------------------------+
  * | This source file is subject to version 2.02 of the PHP license,      |
  * | that is bundled with this package in the file LICENSE, and is        |
  * | available at through the world-wide-web at                           |
  * | http://www.php.net/license/2_02.txt.                                 |
  * | If you did not receive a copy of the PHP license and are unable to   |
  * | obtain it through the world-wide-web, please send a note to          |
  * | license@php.net so we can mail you a copy immediately.               |
  * +----------------------------------------------------------------------+
  * | Author:      Hugo Sacramento - S4F      <hugo@feapa.com.br>          |
  * | Contribution:Pablo Dall'Oglio- Solis    UTF fixbugs                  |
  * +----------------------------------------------------------------------+
  *
  * $Id: XmlArray.lib,v 1.0 2004-04-05 18:10:00 $
  */

/**
  * Class for get array of XML files.
  * @author Hugo Sousa Sacramento <hugo@feapa.com.br>
  * @date 2004-04-05
  */
class XmlArray
{     
    var $xmlFile;
    var $docElement;
    var $document;
    
    function XmlArray($FileName)
    {
        if (substr(PHP_VERSION, 0, 1) == '5')
        {
            $file = @file($FileName);
            if (!$file)
            {
                new Dialog(_a('Permission Denied'), true, false, _a('File') . ': ' . $FileName);
                return false;
            }
            $string = utf8_encode(implode("\n" , $file));
            $this->document = DOMDocument::loadXML($string);
        }
        else
        {
            $file = @file($FileName);
            if (!$file)
            {
                new Dialog(_a('Permission Denied'), true, false, _a('File') . ': ' . $FileName);
                return false;
            }
            $string = utf8_encode(implode("\n" , $file));
            if (function_exists('domxml_open_mem'))
            {
                $this->document = domxml_open_mem($string);
            }
            else
            {
                new Dialog(_a('XML support is not enabled'));
            }
        }
    }
    
    
    function getXmlArray($Node = null) 
    {
        if (substr(PHP_VERSION, 0, 1) == '5')
        {
            $Node = $Node == null ? $this->document->firstChild : $Node;
            if ($nextNode = $Node->firstChild)
            {
                while($nextNode)
                {
                    if ($nextNode->nodeType == XML_ELEMENT_NODE)
                    {
                        if ($nextNode->hasChildNodes())
                        {
                            // if is set, case of more groups ... <group>sdf</group>
                            if ($array[$nextNode->nodeName])
                            {
                                
                                $aux = $array[$nextNode->nodeName];
                                $array[$nextNode->nodeName] = array();
                                $array[$nextNode->nodeName][] = $aux;
                                $array[$nextNode->nodeName][] = $this->getXmlArray($nextNode);
                            }
                            else
                            {
                                $array[$nextNode->nodeName] = $this->getXmlArray($nextNode);
                            }
                        }
                    }
                    elseif ($nextNode->nodeType == XML_TEXT_NODE)
                    {
                        if (trim($nextNode->nodeValue) != "")
                        {
                            $array = ereg_replace("\n\n","\n",utf8_decode($nextNode->nodeValue));
                            $array = str_replace('&lt;', '<', $array);
                            $array = str_replace('&gt;', '>', $array);
                        }
                    }
                    $nextNode = $nextNode->nextSibling;
                }
            }
        }
        else
        {
            $Node = $Node == null ? $this->document->document_element() : $Node;
            if ($nextNode = $Node->first_child())
            {
                while($nextNode)
                {
                    if ($nextNode->node_type() == XML_ELEMENT_NODE)
                    {
                        if ($nextNode->has_child_nodes())
                        {
                            // if is set, case of more groups ... <group>sdf</group>
                            if ($array[$nextNode->node_name()])
                            {
                                $aux = $array[$nextNode->node_name()];
                                $array[$nextNode->node_name()] = array();
                                $array[$nextNode->node_name()][] = $aux;
                                $array[$nextNode->node_name()][] = $this->getXmlArray($nextNode);
                            }
                            else
                            {
                                $array[$nextNode->node_name()] = $this->getXmlArray($nextNode);
                            }
                        }
                    }
                    elseif ($nextNode->node_type() == XML_TEXT_NODE)
                    {
                        if (trim($nextNode->node_value()) != "")
                        {
                            $array = ereg_replace("\n\n","\n",utf8_decode($nextNode->node_value()));
                            $array = str_replace('&lt;', '<', $array);
                            $array = str_replace('&gt;', '>', $array);
                        }
                    }
                    $nextNode = $nextNode->next_sibling();
                }
            }
        }
        return $array;
    }
}
?>
