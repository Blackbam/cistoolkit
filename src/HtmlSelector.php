<?php
namespace CisTools;

/**
 * Class Selector: This class is for building HTML selectors
 * @package CisTools
 * @author Blackbam
 * @copyright (c) 2017 , David Stöckl
 *
 * Author URL: http://www.blackbam.at/
 * Created: 17.07.2017
 */
class HtmlSelector {

    /**
     * Returns a ready HTML selector for a given PHP array.
     *
     * @param array $array: The array to build the selector from.
     * @param string $name: The name which the selector should have.
     * @param boolean $empty (optional, default true): If there should be the possibility to select nothing.
     * @param boolean $use_assoc (optional, default false): Select true, if the option VALUES should be the array KEYs.
     * @param boolean $two_dim (optional, default false): If you have a two-dimensional array.
     * @param string $id: The ID attribute for the selector, if needed.
     * @param mixed $preselect: The exakt value to preselect.
     * @param bool $multiple: If multi-select with array preselect.
     * @return string: The ready HTML.
     */
    public static function fromArray(array $array,string $name,bool $empty=true,bool $use_assoc=false,bool $two_dim=false,string $id="",$preselect=null,bool $multiple=false): string {

        // if multiple, make sure name is array
        if($multiple) {
            if(!(substr($name, -2) === "[]")) {
                $name = $name."[]";
            }

            if(!is_array($preselect)) {
                if($preselect) {
                    $preselect = [$preselect];
                } else {
                    $preselect = [];
                }
            }
        }

        $res = "<select name='".$name."' ".(($id!="") ? "id='".$id."'" :"")." ".(($multiple!="") ? "multiple='".$multiple."'" :"").">";
        if($empty===true) {
            $res .= '<option></option>';
        }
        foreach($array as $key => $value) {
            $prstr = "";

            if($use_assoc==false && $two_dim ==false) {
                if($multiple) {
                    if(in_array($value,$preselect)) {
                        $prstr = 'selected="selected"';
                    }
                } else if($preselect==$value) {
                    $prstr = 'selected="selected"';
                }
                $res .= '<option '.$prstr.'>'.$value.'</option>';
            } else if($use_assoc==true && $two_dim==false) {

                if($multiple) {
                    if(in_array($key,$preselect)) {
                        $prstr = 'selected="selected"';
                    }
                } else if($preselect==$key) {
                    $prstr = 'selected="selected"';
                }
                $res .= '<option value="'.$key.'" '.$prstr.'>'.$value.'</option>';
            } else if($use_assoc==false && $two_dim==true) {
                $res .= '<optgroup label="'.$key.'">';
                foreach($value as $op) {
                    if($multiple) {
                        if(in_array($op,$preselect)) {
                            $prstr = 'selected="selected"';
                        }
                    } else if($preselect==$op) {
                        $prstr = 'selected="selected"';
                    }
                    $res .= '<option '.$prstr.'>'.$op.'</option>';
                }
                $res .= '</optgroup>';
            } else if($use_assoc==true && $two_dim==true) {
                $res .= '<optgroup label="'.$key.'">';
                foreach($value as $inner_key => $inner_value) {
                    if($multiple) {
                        if(in_array($inner_key,$preselect)) {
                            $prstr = 'selected="selected"';
                        }
                    } else if($preselect==$inner_key) {
                        $prstr = 'selected="selected"';
                    }
                    $res .= '<option value="'.$inner_key.'" '.$prstr.'>'.$inner_value.'</option>';
                }
                $res .= '</optgroup>';
            }
        }
        return $res."</select>";
    }

}