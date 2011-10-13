<?php

class p {
    
    function p() {
        print "Parent's constructor\n";
    }
    
    function p_test() {
        print "p_test()\n";
        $this->c_test();
    }
}

class c extends p {
    
    function c() {
        print "Child's constructor\n";
        parent::p();
    }
    
    function c_test() {
        print "c_test()\n";
    }
}

$obj = new c;
$obj->p_test();

?>