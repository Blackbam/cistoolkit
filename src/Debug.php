<?php
namespace CisTools;

/**
 * Class Debug: Debugging helpers
 * @package CisTools
 */
class Debug {

    /**
     * For pretty dumping of variables to an HTML output
     *
     * @param mixed $var: The variable to dump pretty
     */
    public static function dump($var): void {
        echo "<pre>";
        var_dump($var);
        echo "</pre>";
    }

    /**
     * WARNING: This is not a perfect dump of a closure, it just should help you find it.
     *
     * @param Closure $c: A variable holding a Closure
     * @param bool $echo: False to not echo the output
     * @return string
     */
    public static function dumpClosure(\Closure $c, bool $echo = true): string {
        $str = 'function (';
        try {
            $r = new \ReflectionFunction($c);
        } catch(\ReflectionException $exception) {
            // do nothing
        }
        $params = array();
        foreach($r->getParameters() as $p) {
            $s = '';
            if($p->isArray()) {
                $s .= 'array ';
            } else if($p->getClass()) {
                $s .= $p->getClass()->name . ' ';
            }
            if($p->isPassedByReference()){
                $s .= '&';
            }
            $s .= '$' . $p->name;
            if($p->isOptional()) {
                try {
                $s .= ' = ' . var_export($p->getDefaultValue(), true);
                } catch(\ReflectionException $exception){
                    // do nothing
                }
            }
            $params []= $s;
        }
        $str .= implode(', ', $params);
        $str .= '){' . PHP_EOL;
        $lines = file($r->getFileName());
        for($l = $r->getStartLine(); $l < $r->getEndLine(); $l++) {
            $str .= $lines[$l];
        }
        if($echo) {
            echo $str;
        }
        return $str;
    }
}