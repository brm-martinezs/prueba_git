<?php
/**
 * PEAR::DB_DataObject Data Source Driver
 * 
 * PHP versions 4 and 5
 *
 * LICENSE:
 * 
 * Copyright (c) 1997-2007, Andrew Nagy <asnagy@webitecture.org>,
 *                          Olivier Guilyardi <olivier@samalyse.com>,
 *                          Mark Wiesemann <wiesemann@php.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the 
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products 
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * CSV file id: $Id: DataObject.php,v 1.44 2007/05/07 21:06:53 olivierg Exp $
 * 
 * @version  $Revision: 1.44 $
 * @package  Structures_DataGrid_DataSource_DataObject
 * @category Structures
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 */


require_once 'Structures/DataGrid/DataSource.php';

/**
 * PEAR::DB_DataObject Data Source Driver
 *
 * This class is a data source driver for a PEAR::DB::DB_DataObject object
 *
 * SUPPORTED OPTIONS:
 *
 * - labels_property:  (string)  The name of a property that you can set within
 *                               your DataObject. This property is expected to
 *                               contain the same kind of information as the
 *                               'labels' option. If the 'labels' option is set,
 *                               this one will not be used.
 * - fields_property:  (string)  The name of a property that you can set within
 *                               your DataObject. This property is expected to
 *                               contain the same kind of information as the
 *                               'fields' option. If the 'fields' option is set,
 *                               this one will not be used.
 * - raw_count:        (bool)    If true: query all the records in order to
 *                               count them. This is needed when records are 
 *                               grouped (GROUP BY, DISTINCT, etc..), but
 *                               might be heavy.
 *                               If false: perform a smart count query with 
 *                               DB_DataObject::count().
 * 
 * @version  $Revision: 1.44 $
 * @author   Olivier Guilyardi <olivier@samalyse.com>
 * @author   Andrew Nagy <asnagy@webitecture.org>
 * @access   public
 * @package  Structures_DataGrid_DataSource_DataObject
 * @category Structures
 */
class Structures_DataGrid_DataSource_DataObject
    extends Structures_DataGrid_DataSource
{   
    /**
     * Reference to the DataObject
     *
     * @var object DB_DataObject
     * @access private
     */
    var $_dataobject;
    
    /**
     * Total number of rows 
     * 
     * This property caches the result of DataObject::count(), that 
     * can't be called after DataObject::fetch() (DataObject bug?).
     *
     * @var int
     * @access private
     */
     var $_rowNum = null;    
    
    /**
     * Constructor
     *
     * @param object DB_DataObject
     * @access public
     */
    function Structures_DataGrid_DataSource_DataObject()
    {
        parent::Structures_DataGrid_DataSource();

        $this->_addDefaultOptions(array(
                    'use_private_vars' => false,
                    'labels_property' => 'fb_fieldLabels',
                    'fields_property' => 'fb_fieldsToRender',
                    'sort_property' => 'fb_linkOrderFields',
                    'formbuilder_integration' => false,
                    'raw_count' => false));
       
        $this->_setFeatures(array('multiSort' => true));
    }
  
    /**
     * Bind
     *
     * @param   object DB_DataObject    $dataobject     The DB_DataObject object
     *                                                  to bind
     * @param   array                   $options        Associative array of 
     *                                                  options.
     * @access  public
     * @return  mixed   True on success, PEAR_Error on failure
     */
    function bind(&$dataobject, $options = array())
    {
        if ($options) {
            $this->setOptions($options); 
        }

        if (is_subclass_of($dataobject, 'DB_DataObject')) {
            $this->_dataobject =& $dataobject;

            $mergeOptions = array();
            
            // Merging the fields and fields_property options
            if (!$this->_options['fields']) {
                if ($fieldsVar = $this->_options['fields_property']
                    and isset($this->_dataobject->$fieldsVar)) {
                    $mergeOptions['fields'] = $this->_dataobject->$fieldsVar;
                    if ($this->_options['formbuilder_integration']) {
                        if (isset($this->_dataobject->fb_preDefOrder)) {
                            $ordered = array();
                            foreach ($this->_dataobject->fb_preDefOrder as
                                     $orderField) {
                                if (in_array($orderField,
                                             $mergeOptions['fields'])) {
                                    $ordered[] = $orderField;
                                }
                            }
                            $mergeOptions['fields'] =
                                array_merge($ordered,
                                            array_diff($mergeOptions['fields'],
                                                       $ordered));
                        }
                        foreach ($mergeOptions['fields'] as $num => $field) {
                            if (strstr($field, '__tripleLink_') ||
                                strstr($field, '__crossLink_') || 
                                strstr($field, '__reverseLink_')) {
                                unset($mergeOptions['fields'][$num]);
                            }
                        }
                    }
                }
            }

            // Merging the labels and labels_property options
            if (!$this->_options['labels'] 
                and $labelsVar = $this->_options['labels_property']
                and isset($this->_dataobject->$labelsVar)) {
                
                $mergeOptions['labels'] = $this->_dataobject->$labelsVar;

            }

            if ($mergeOptions) {
                $this->setOptions($mergeOptions);
            }
                
            return true;
        } else {
            return PEAR::raiseError('The provided source must be a DB_DataObject');
        }
    }

    /**
     * Fetch
     *
     * @param   integer $offset     Limit offset (starting from 0)
     * @param   integer $len        Limit length
     * @access  public
     * @return  array   The 2D Array of the records
     */    
    function &fetch($offset = 0, $len = null)
    {
        // Check to see if Query has already been submitted
        if ($this->_dataobject->getDatabaseResult()) {
            $this->_rowNum = $this->_dataobject->N;
        } else {
            // Caching the number of rows
            if (PEAR::isError($count = $this->count())) {
                return $count;
            } else {
                $this->_rowNum = $count;
            }
                    
            // Sorting
            if (($sortProperty = $this->_options['sort_property'])
                      && isset($this->_dataobject->$sortProperty)) {
                foreach ($this->_dataobject->$sortProperty as $sort) {
                    $this->sort($sort);
                }
            }
            
            // Limiting
            if ($offset) {
                $this->_dataobject->limit($offset, $len);
            } elseif ($len) {
                $this->_dataobject->limit($len);
            }
            
            $result = $this->_dataobject->find();
        }
        
        // Retrieving data
        $records = array();
        if ($this->_rowNum) {
            if ($this->_options['formbuilder_integration']) {
                require_once('DB/DataObject/FormBuilder.php');
                $links = $this->_dataobject->links();
            }           
            $initial = true;
            while ($this->_dataobject->fetch()) {
                // Determine Fields
                if ($initial) {
                    if (!$this->_options['fields']) {
                        if ($this->_options['use_private_vars']) {
                            $this->_options['fields'] =
                                array_keys(get_object_vars($this->_dataobject));
                        } else {
                            $this->_options['fields'] =
                                array_keys($this->_dataobject->toArray());
                        }
                    }
                    $initial = false;
                }
                // Build DataSet
                $rec = array();
                foreach ($this->_options['fields'] as $fName) {
                    $getMethod = (strpos($fName, '_') !== false) 
                        ? 'get' . implode('', array_map('ucfirst', 
                                            explode('_', $fName)))
                        : 'get' . ucfirst($fName);
                    if (method_exists($this->_dataobject, $getMethod)) {
                        $rec[$fName] = $this->_dataobject->$getMethod();
                    } elseif (isset($this->_dataobject->$fName)) {                        
                        $rec[$fName] = $this->_dataobject->$fName;
                    } else {
                        $rec[$fName] = null;
                    }
                }
                
                // Get Linked FormBuilder Fields
                if ($this->_options['formbuilder_integration']) {
                    foreach (array_keys($rec) as $field) {
                        if (isset($links[$field]) &&
                            isset($this->_dataobject->$field) &&
                            $linkedDo =& $this->_dataobject->getLink($field) &&
                            !PEAR::isError($linkedDo)) {
                            $rec[$field] = DB_DataObject_FormBuilder::getDataObjectString($linkedDo);
                        }
                    }
                }
                                
                $records[] = $rec;
            }
        }

        // TODO: (maybe) free the result object here

        return $records;
    }

    /**
     * Count
     *
     * @access  public
     * @return  int         The number of records or a PEAR_Error
     */    
    function count()
    {
        if (is_null($this->_rowNum)) {
            if ($this->_dataobject->N) {
                $this->_rowNum = $this->_dataobject->N;
            } else {
                if ($this->_options['raw_count']) {
                    $clone = clone($this->_dataobject);
                    $clone->orderBy(); // Clear unneeded ordering
                    $test = $clone->find();
                    if (!is_numeric($test)) {
                        return PEAR::raiseError('Can\'t count the number of rows');
                    }
                    $clone->free();
                } else {
                    $test = $this->_dataobject->count();
                    if ($test === false) {
                        return PEAR::raiseError('Can\'t count the number of rows');
                    }
                }

                $this->_rowNum = $test;
            }
        }

        return $this->_rowNum;
    }
    
    /**
     * Sorts the dataobject.  This MUST be called before fetch.
     * 
     * @access  public
     * @param   mixed   $sortSpec   A single field (string) to sort by, or a 
     *                              sort specification array of the form:
     *                              array(field => direction, ...)
     * @param   string  $sortDir    Sort direction: 'ASC' or 'DESC'
     *                              This is ignored if $sortDesc is an array
     */
    function sort($sortSpec, $sortDir = null)
    {
        $db = $this->_dataobject->getDatabaseConnection();

        if (is_array($sortSpec)) {
            foreach ($sortSpec as $field => $direction) {
                $field = $db->quoteIdentifier($field);
                $this->_dataobject->orderBy("$field $direction");
            }
        } else {
            $sortSpec = $db->quoteIdentifier($sortSpec);
            if (is_null($sortDir)) {
                $this->_dataobject->orderBy($sortSpec);
            } else {
                $this->_dataobject->orderBy("$sortSpec $sortDir");
            }
        }
    }
    
    // This function is temporary until DB_DO bug #1315 is fixed
    // This removeds and variables from the DataObject that begins with _ or fb_
    function _fieldsFilter($value)
    {
        if (substr($value, 0, 1) == '_') {
            return false;
        } else if (substr($value, 0, 3) == 'fb_') {
            return false;
        } else if ($value == 'N') {
            return false;
        } else {
            return true;
        }
        
    }

}

/*
 * clone() replacement, (partly) by Aidan Lister <aidan@php.net>
 * borrowed from PHP_Compat 1.5.0
 */
if ((version_compare(phpversion(), '5.0') === -1) && !function_exists('clone')) {
    // Needs to be wrapped in eval as clone is a keyword in PHP5
    eval('
        function clone($object)
        {
            // Sanity check
            if (!is_object($object)) {
                user_error(\'clone() __clone method called on non-object\', E_USER_WARNING);
                return;
            }
    
            // Use serialize/unserialize trick to deep copy the object
            $object = unserialize(serialize($object));

            // If there is a __clone method call it on the "new" class
            if (method_exists($object, \'__clone\')) {
                $object->__clone();
            }
            
            return $object;
        }
    ');
}

/* vim: set expandtab tabstop=4 shiftwidth=4: */
?>
