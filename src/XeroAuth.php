<?php
/**
 * Created by PhpStorm.
 * User: sysadmin
 * Date: 22/06/15
 * Time: 12:41
 */

namespace WebDevBren\Xero;

use Illuminate\Support\Collection;
use XeroOAuth;

class XeroAuth {

    protected $xero;

    public function __construct( array $Config = null) {

        if( is_null($Config) ) {
            $Config = \Config::get('xero.default');
        }

        $this->xero = XeroOAuth($Config);

    }

    /**
     * @param array $params
     */
    public function doAuth($params = []) {

        $this->xero->request ( 'GET', $this->xero->url ( 'RequestToken', '' ), $params );

        if ($this->xero->response ['code'] == 200) {

            $scope = "";
            // $scope = 'payroll.payrollcalendars,payroll.superfunds,payroll.payruns,payroll.payslip,payroll.employees,payroll.TaxDeclaration';
            if ($_REQUEST ['authenticate'] > 1)
                $scope = 'payroll.employees,payroll.payruns,payroll.timesheets';

            print_r ( $this->xero->extract_params ( $this->xero->response ['response'] ) );
            $_SESSION ['oauth'] = $this->xero->extract_params ( $this->xero->response ['response'] );

            $authurl = $this->xero->url ( "Authorize", '' ) . "?oauth_token={$_SESSION['oauth']['oauth_token']}&scope=" . $scope;

            header('location:'.$authurl);
            return '<p>To complete the OAuth flow follow this URL: <a href="' . $authurl . '">' . $authurl . '</a></p>';
        } else {
            outputError ( $this->xero );
        }
    }


    public function checkAuth(\Closure $callback) {
        if (isset ( $_REQUEST ['oauth_verifier'] )) {
            $this->xero->config ['access_token'] = \Session::get('oauth')['oauth_token'];
            $this->xero->config ['access_token_secret'] = \Session::get('oauth')['oauth_token_secret'];

            $response = $this->xero->request ( 'GET', $this->xero->url ( 'AccessToken', '' ), array (
                'oauth_verifier' => $_REQUEST ['oauth_verifier'],
                'oauth_token' => $_REQUEST ['oauth_token']
            ) );

            if ($this->xero->response ['code'] == 200) {

                $response = $this->xero->extract_params ( $this->xero->response ['response'] );
                $session = persistSession ( $response );

                \Session::forget('oauth');
                header ( "Location: {$here}" );

                return $callback;

            } else {
                outputError ( $this->xero );
            }
            // start the OAuth dance
        }
    }

    public function PostInvoice($information = array()) {
        // checks if the Array is Informational or has Actual info and Generates Code
        if($information[0]) {
            $rxml = "";
            foreach($information as $inf) {
              $rxml .= generateXML("Invoice", $inf);
            }
            $xml = "<Invoices>".$rxml."</Invoices>";
        } else {
            $xml = "<Invoices>".generateXML("Invoice", $information)."</Invoices>";
        }

        $this->xero->request('POST', $this->xero->url('Invoices', 'core'), array(), $xml);

        return $this->xero->response;
    }

    public function PostContact(Collection $Users) {

    }

    public function generateXML($type = "Invoice", $info = array()) {
        switch($type){
            case "Invoice":
            case "invoice":

            $xml = "<Invoice>";

                foreach($info as $invoice) {
                    foreach ($invoice as $key => $value) {
                        if(is_array($value)) {
                            $sxml = "<$key>";
                            foreach($value as $k => $v) {
                                $sxml .= "<$k> $v </$k>";
                            }
                            $sxml .= "</$key";
                            $xml .= $sxml;
                        }
                            $xml .= "<$key> $value </$key>";
                    }
                }

                break;
            case "Contact":

                break;
        }

        return $xml;
    }





}