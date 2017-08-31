<?php

/*

Smarty plugin

--------------------------------------------------------------------------------

File: function.html_group_widget.php
Type: function
Name: html_radios
Version: 1.2
Date: 20041218
Purpose: Prints out a list of radio input types or checkboxes
Input: name (optional) - string default "radio"
values (required) - array
options (optional) - associative array
checked (optional) -
   array for checkbox
   string for radio
separator (optional) - ie <br /> or &
class (optional) - name of the class for the radios.
output (optional) - without this one the buttons don't have names
position (optional) - place the output to the left or right of the radio button. default right side
withlabels (optional) - put a <label> tag around the output with for= attrib the same as the id
   Note: without specifying withlabels, no output is shown beside checkboxes/radios
withids (optional) - put an attribute within the <input type="radio" tag
events (optional) - put specific events codes per option/output, eg add
onclick="doSomething(this)" onchange="doSomethingElse(this)"
as an associative array per the keys in options,
or as an array per the values in values
assign (optional) - assign the output as an array to this variable
Author: Christopher Kvarme <christopher.kvarme@flashjab.com>
Credits: Monte Ohrt <monte@ispi.net>
Modified: [20030928] Bill Wheaton <billwheaton@mindspring.com>
Examples: {html_radios values=$ids output=$names}
{html_radios values=$ids name='box' separator='<br>' output=$names}
{html_radios values=$ids checked=$checked separator='<br>' output=$names}
{html_radios values=$ids checked=$checked separator='<br>' output=$names events=$events}
where $events can be an array if not using 'options' as so :
$events = array(
array(
'onclick'=>"alert('clicked option 0')",
'onblur'=>"alert('went to another place from option 0')"
),
array(
'onclick'=>"alert('clicked option 1')",
'onblur'=>"alert('went to another place from option 1')"
)
);
or if using 'options' an $events associative array with the same keys,
eg 'yes'=>'Yes','no'=>'No' as so:
$events = array(
'yes' => 'array(
'onclick'=>"alert('clicked Yes')",
'onblur'=>"alert('went to another place from Yes')"
),
'no' => array(
'onclick'=>"alert('clicked No')",
'onblur'=>"alert('went to another place from No')"
)
);
Modified: [20040823] Christopher M. Black <developer@devonium.com> website: http://www.devonium.com
Fixed id identifiers and label tags to appropriately generate the HTML
Modified: [20041218] Joshua Morse <jmorse@fuse.net>
Fixed bug with how selection of radio/checkboxes was handled.
Added added an assign parameter (credit goes to the current html_checkboxes function of smarty).
When the assign parameter is specified, the output of the function is saved in the specified variable,
instead of being output as HTML (very useful for columnizing data in a table, for instance).

--------------------------------------------------------------------------------
*/

function smarty_function_html_group_widget($params, &$smarty)
{
   require_once $smarty->_get_plugin_filepath('shared', 'escape_special_chars');
   $name = 'radio';
   $type = 'radio';
   $values = null;
   $options = null;
   $selected = null;
   $separator = '';
   $output = null;
   $events = null;
   $withlabels = false;
   $titles = null;
   $tab_orders = null;
   $access_keys = null;
   $position = true;
   $withids = false;
   $extra = "";
   $class = "";
   $style = "";
   $assign = "";
   $assign_temp = array();
   $type = isset($params['type']) ? (string)$params['type'] : $type; //radio or checkbox

   foreach($params as $_key => $_val)
   {
      switch($_key)
      {
         case 'type': // radio or checkbox? def = 'radio'
         case 'name':
         case 'class':
         case 'separator':
         case 'style':
            $$_key = (string)$_val;
            break;

         case 'withids':
         case 'withlabels':
            /* We need ids for the labels to work */
            $params["withids"] = true;
            $$_key = (bool)$_val;
            break;

         case 'checked':
         case 'selected':
            if((string)$_val != null)
            {
               if ($type == 'radio')
               {
                  if(is_array($_val))
                  {
                     $smarty->trigger_error('html_radios: the "' . $_key . '" attribute cannot be an array', E_USER_WARNING);
                  }
                  else
                  {
                     $selected = (string)$_val;
                  }
               }
               elseif (is_array($_val))
               {
                  $selected = (array)$_val;
               }
               else
               {
                  $selected = (string)$_val;
               }
            }
            break;

         case 'access_keys':
         case 'tab_orders':
         case 'titles':
         case 'events':
         case 'options':
            $$_key = (array)$_val;
            break;

         case 'values':
         case 'outputs':
         case 'output':
            $$_key = array_values((array)$_val);
            break;

         case 'radios':
            $smarty->trigger_error('html_radios: the use of the "radios" attribute is deprecated, use "options" instead', E_USER_WARNING);
            $options = (array)$_val;
            break;

         case 'assign':
            break;

         default:
            $extra .= ' '.$_key.'="'.smarty_function_escape_special_chars((string)$_val).'"';
            break;

      }
   }

   if ( !isset($options) && !isset($values) )
   {
      return ''; /* raise error here? */
   }

   $_html_result = '';

   if (isset($options) && is_array($options))
   {
      foreach ((array)$options as $_key=>$_val)
      {
         $_events = isset($events[$_key]) ? $events[$_key] : '';
         $_title = isset($titles[$_key]) ? $titles[$_key] : '';
         $_access_key = isset($access_keys[$_key]) ? $access_keys[$_key] : '';
         $_tab_order = isset($tab_orders[$_key]) ? $tab_orders[$_key] : '';

         if(!empty($params['assign'])) {
            $assign_temp[] = smarty_function_html_field_group_output($type, $name, $_key, $_val, $_i, $selected, $_events, $_title, $_access_key, $_tab_order, $withids, $extra, $separator, $position, $withlabels);
         }
         else
         {
            $_html_result .= smarty_function_html_field_group_output($type, $name, $_key, $_val, $_i, $selected, $_events, $_title, $_access_key, $_tab_order, $withids, $extra, $separator, $position, $withlabels);
         }
      }
   }
   else
   {
      foreach ((array)$values as $_i=>$_key)
      {
         $_val = isset($output[$_i]) ? $output[$_i] : '';
         $_events = isset($events[$_i]) ? $events[$_i] : '';
         $_title = isset($titles[$_i]) ? $titles[$_i] : '';
         $_access_key = isset($access_keys[$_i]) ? $access_keys[$_i] : '';
         $_tab_order = isset($tab_orders[$_i]) ? $tab_orders[$_i] : '';

         if(!empty($params['assign'])) {
            $assign_temp[] = smarty_function_html_field_group_output($type, $name, $_key, $_val, $_i, $selected, $_events, $_title, $_access_key, $_tab_order, $withids, $extra, $separator, $position, $withlabels);
         }
         else
         {
            $_html_result .= smarty_function_html_field_group_output($type, $name, $_key, $_val, $_i, $selected, $_events, $_title, $_access_key, $_tab_order, $withids, $extra, $separator, $position, $withlabels);
         }
      }
   }

   if(!empty($params['assign'])) {
      $smarty->assign($params['assign'], $assign_temp);
   }
   else
   {
      return $_html_result;
   }

}

function smarty_function_html_field_group_output($type, $name, $value, $output, $index, $selected, $events, $title, $access_key, $tab_order, $withids, $extra, $separator, $position, $withlabels )
{
   // probably should put something for accessKey too... naugh
   if ($withlabels)
   {
      $_output = "<label for=\"label" . smarty_function_escape_special_chars($name) . $value . "\"";

      if($access_key)
      {
         $_output .= ' accesskey="' . smarty_function_escape_special_chars($access_key) . '"';
      }

      if($tab_order)
      {
         $_output .= ' taborder="' . smarty_function_escape_special_chars($tab_order) . '"';
      }

      if($title)
      {
         $_output .= " title=\"" . smarty_function_escape_special_chars($title) . "\"";
      }

      $_output .= ">" . $output . "</label>";
   }

   $_element = '<input type="'.$type.'" name="'
   . smarty_function_escape_special_chars($name) . '" value="'
   . smarty_function_escape_special_chars($value) . '"';

   if ( isset($selected) )
   {
      if ( $type == 'radio' || !is_array($selected) )
      {
         if ($index==$selected)
         {
            $_element .= ' checked="checked"';
         }
      }
      else
      {

         if($selected[$index] == 1)
         {
            $_element .= ' checked="checked"';
         }
      }
   }

   foreach ((array)$events as $eventname => $eventcode)
   {
      $_element .= " " . $eventname . "=\"". $eventcode . "\"";
   }

   if($title)
   {
      $_element .= ' title="' . smarty_function_escape_special_chars($title) . '"';
   }

   if($withids)
   {
      $_element .= " id=\"label" . smarty_function_escape_special_chars($name) . $value . "\"";
   }

   if($class)
   {
      $_element .= " class=\"" . smarty_function_escape_special_chars($class) . "\"";
   }

   $_element .= " " . $extra . " />";

   if($position)
   {
      $_element .= $_output . $separator . "\n";
   }
   else
   {
      $_element = $_output . $_element . $separator . "\n";
   }

   return $_element;
}
?>