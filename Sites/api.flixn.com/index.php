<?php
// XXX: Proper XML headers

require 'Ex/Global.php';
$fse = new FlixnServicesErrors();

try {
    $s = new ExServices();
    $sd = new ExServicesDispatch($s);
    print $sd->dispatch();
} catch (PDOException $e) {

    // XXX: PDOException? This is lame.

    $r = new ExXMLElement('error', $fse->getPrintableError(FlixnServicesErrors::INTERNAL_ERROR) .':'. $e->getMessage() .':'. $e->getTraceAsString());
    $r->setAttribute('code', FlixnServicesErrors::INTERNAL_ERROR);
    print $r->render();

} catch (Exception $e) {

    /**
     *
     * We only support rest-style XML error responses for now
     *
     */

//    $r = new ExXMLElement('error', $fse->getPrintableError($e->getMessage()));
    $r = new ExXMLElement('error', $e->getTraceAsString());
    $r->setAttribute('code', $e->getMessage());
    print $r->render();
}
