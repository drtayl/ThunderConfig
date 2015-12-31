<?php

/* 
 * Thunderbird Simple MultiDomain Autoconfig script.
 * 
 *  @author Drew Taylor <Drew.Taylor@TaylorsConsulting.com>
 *  @license GPL v2
 */


header("Content-Type: text/xml"); 
$config = new ThunderConfig();
$config->display_document();

Class ThunderConfig {
    
    
    /*
     * This is the name that will show up in the XML as your mail provider name.
     */
    public $emailProvider = "example.com";
        
    /*
     * Set your mail server DNS name here. If you have a more complex deployment
     * you can set more specific hostnames later.
     */
    public $default_mail_hostname = "mail.example.com";        
    
    /*
     * Sets the url to refer to internally for documentation for values to create xml file
     * 
     * this is required. 
     *  https://developer.mozilla.org/en-US/docs/Mozilla/Thunderbird/Autoconfiguration/FileFormat/HowTo#Documentation.C2.A0URL
     */
    public $documentation_url = "https://example.com/email-setup.html";
    public $documentation_lang = "en";
    public $documentation_desc = "Example Support";
    
    /* 
     * Specify the default socket type. this can be overriden later in
     * the configuration. 
     * 
     * Options:
     * 
     *  "plain"
     *  "SSL"
     *  "STARTTLS"     
     * 
     *  For further info: https://wiki.mozilla.org/Thunderbird:Autoconfiguration:ConfigFileFormat
     */
    //Refer to Incoming and Outgoing configuration. No global default setting.
    
    /* 
     * Specify the default authentication type. this can be overriden later in
     * the configuration. 
     * 
     * Options:
     * 
     *  "plain"
     *  "password-cleartext"
     *  "password-encrypted"
     *  "secure"
     *  "NTLM"
     *  "GSSAPI"
     *  "client-IP-address"
     *  "TLS-client-cert"
     *  "none"
     * 
     *  For further info: https://wiki.mozilla.org/Thunderbird:Autoconfiguration:ConfigFileFormat
     */
    public $default_auth_type = "password-cleartext";
    
    
    /*
     * Display Name in Thunderbird representing the account
     * 
     * if unset, it will default to the email domain of the user.
     */
    public $displayName = null;
    
    /*
     * Short Display Name
     * 
     * if unset, the email domain will be modified to fill this field.
     */
    public $displayName_Short = null;            
    
    //Incoming configuration
    
    public $incoming = array(
      0  => array(
          'enabled' => true,
          'type' => "imap", //imap, pop3, smtp
          'mail_host' => null, //set to null to use default, else specify.
          'auth_type' => null, //set to null to use default, else specify.
          'socket_type' => 'STARTTLS',          
          'port' => 143
      ),
      1  => array(
          'enabled' => false,
          'type' => "imap", //imap, pop3, smtp
          'mail_host' => null, //set to null to use default, else specify.
          'auth_type' => null, //set to null to use default, else specify.
          'socket_type' => 'SSL',
          'port' => 993
      ),      
      2  => array(
          'enabled' => false,
          'type' => "imap", //imap, pop3, smtp
          'mail_host' => null, //set to null to use default, else specify.
          'auth_type' => null, //set to null to use default, else specify.
          'socket_type' => 'plain',
          'port' => 143
      ),
    );
    
    //Outgoing configuration
    
    public $outgoing = array(
      0  => array(
          'enabled' => true,
          'type' => "smtp", //imap, pop3, smtp
          'mail_host' => null, //set to null to use default, else specify.
          'auth_type' => null, //set to null to use default, else specify.
          'addThisServer' => "true", // true or false
          'GlobalPreferredServer' => "true", // true or false
          'socket_type' => 'STARTTLS',          
          'port' => 587
      ),
      1  => array(
          'enabled' => false,
          'type' => "smtp", //imap, pop3, smtp
          'mail_host' => null, //set to null to use default, else specify.
          'auth_type' => null, //set to null to use default, else specify.
          'addThisServer' => "true", // true or false
          'GlobalPreferredServer' => "false", // true or false
          'socket_type' => 'SSL',
          'port' => 465
      ),
      2  => array(
          'enabled' => false,
          'type' => "smtp", //imap, pop3, smtp
          'mail_host' => null, //set to null to use default, else specify.
          'auth_type' => null, //set to null to use default, else specify.
          'addThisServer' => "true", // true or false
          'GlobalPreferredServer' => "false", // true or false
          'socket_type' => 'plain',
          'port' => 25
      ),
    );
                          
    private $EmailAddress = null;
    private $EmailDomain = null;    
    private $document = null;
    
    public function __construct() {
        if(isset($_GET['emailaddress']) && !empty($_GET['emailaddress'])){
            $this->EmailAddress = strtolower($_GET['emailaddress']);
        }
        else {
            $this->EmailAddress = "%EMAILADDRESS%";
        }                
        
        $this->split_EmailDomain();
        $this->handle_displaynames();
        $this->build_output();
    }
    
    public function display_document(){
        echo $this->document;
    }

        private function split_EmailDomain(){
        if($this->EmailAddress != "%EMAILADDRESS%"){
            $split_email = explode("@", $this->EmailAddress);
            $this->EmailDomain = $split_email[1];
        } else {
            $this->EmailDomain = "%EMAILDOMAIN%";
        }
    }
    
    private function handle_displaynames(){
        if($this->displayName == null){
            if($this->EmailDomain != "%EMAILDOMAIN%"){
                $this->displayName = $this->EmailDomain;
            } else {
                $this->displayName = "Unkown";
            }
        }
        
        if($this->displayName_Short == null){
            if($this->EmailDomain != "%EMAILDOMAIN%"){
                $this->displayName_Short = str_replace(".","_",$this->EmailDomain);
            } else {
                $this->displayName_Short = "Unkown";
            }
        }
    }
    
    private function build_output(){
        
        $this->document = '<?xml version="1.0"?>'."\r\n";
        $this->document .= '<clientConfig version="1.1">'."\r\n";
        $this->build_emailProvider();
        $this->build_domain();
        $this->build_display_name();
        $this->build_display_shortname();
        $this->build_incoming_servers();
        $this->build_outgoing_servers();
        $this->build_documentation();
        
        $this->document .= '</emailProvider>'."\r\n";
        $this->document .= '</clientConfig>'."\r\n";
    }
    
    private function build_emailProvider(){
        
        $this->document .= '<emailProvider id="';
        
        if($this->emailProvider != null){
            $this->document .= $this->emailProvider;
        } elseif ($this->EmailDomain != "%EMAILDOMAIN%"){
            $this->document .= $this->EmailDomain;
        } else {
            $this->document .= "Unknown";
        }
        
        $this->document .= '">'."\r\n";
    }
    
    private function build_domain(){
        $this->document .= '<domain>'.$this->extract_domain($_SERVER['HTTP_HOST']).'</domain>'."\r\n";
    }
    
    private function extract_domain($domain)
    {
        if(preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $domain, $matches))
        {
            return $matches['domain'];
        } else {
            return $domain;
        }
    }
    
    private function build_display_name(){
        $this->document .= '<displayName>'.$this->displayName.'</displayName>'."\r\n";
    }
    
    private function build_display_shortname(){
        $this->document .= '<displayShortName>'.$this->displayName_Short.'</displayShortName>'."\r\n";
    }
    
    private function build_incoming_servers(){
        
        $incoming_block = "";
        
        foreach($this->incoming as $incoming_key=>$incoming_values){
            if($incoming_values['enabled'] === true){
                $incoming_block .= '<incomingServer type="'.$incoming_values['type'].'">'."\r\n";
                $incoming_block .= $this->build_server_hostname($incoming_values);
                $incoming_block .= '<port>'.$incoming_values['port'].'</port>'."\r\n";
                $incoming_block .= '<socketType>'.$incoming_values['socket_type'].'</socketType>'."\r\n";
                $incoming_block .= '<username>'.$this->EmailAddress.'</username>'."\r\n";
                $incoming_block .= $this->build_server_authentication($incoming_values);
                $incoming_block .= '</incomingServer>'."\r\n";
            }
        }
        
        if(strlen($incoming_block) > 1){
            $this->document .= $incoming_block;
        }
        
    }
    
    
    private function build_outgoing_servers(){
        
        $outgoing_block = "";
        
        foreach($this->outgoing as $outgoing_key=>$outgoing_values){
            if($outgoing_values['enabled'] === true){
                $outgoing_block .= '<outgoingServer type="'.$outgoing_values['type'].'">'."\r\n";
                $outgoing_block .= $this->build_server_hostname($outgoing_values);
                $outgoing_block .= '<port>'.$outgoing_values['port'].'</port>'."\r\n";
                $outgoing_block .= '<socketType>'.$outgoing_values['socket_type'].'</socketType>'."\r\n";
                $outgoing_block .= $this->build_server_authentication($outgoing_values);
                $outgoing_block .= '<username>'.$this->EmailAddress.'</username>'."\r\n";
                $outgoing_block .= '<addThisServer>'.$outgoing_values['addThisServer'].'</addThisServer>'."\r\n";
                $outgoing_block .= '<useGlobalPreferredServer>'.$outgoing_values['GlobalPreferredServer'].'</useGlobalPreferredServer>'."\r\n";
                $outgoing_block .= '</outgoingServer>'."\r\n";
            }
        }
        
        if(strlen($outgoing_block) > 1){
            $this->document .= $outgoing_block;
        }
        
    }
    
    private function build_server_hostname($values){
        $sub_block .= '<hostname>';
        if($values['mail_host'] != null){
            $sub_block .= $values['mail_host'];
        } else {
            $sub_block .= $this->default_mail_hostname;
        }
        $sub_block .= '</hostname>'."\r\n";
        
        return $sub_block;
    }
    
    private function build_server_authentication($values){
        $sub_block .= '<authentication>';
        if($values['auth_type'] != null){
            $sub_block .= $values['auth_type'];
        } else {
            $sub_block .= $this->default_auth_type;
        }
        $sub_block .= '</authentication>'."\r\n";
        
        return $sub_block;
    }
    
    private function build_documentation(){
        $sub_block .= '<documentation url="' . $this->documentation_url. '">'."\r\n";
        $sub_block .= '<descr lang="'. $this->documentation_lang .'">'.$this->documentation_desc.'</descr>';
        $sub_block .= '</documentation>'."\r\n";
        
        $this->document .= $sub_block;
    }
}



