<?php

interface SystemCleanupInterface{
    public function clearSystemFakeEmails();
}

class SystemCleanup extends GamFunctions implements SystemCleanupInterface{

    public function clearSystemFakeEmails(){
        global $wpdb;

        $banned_emails = (new Emails)->getBannedEmails();

        if(!$banned_emails) return $this;

        foreach($banned_emails as $email){

            // invoice 
            $wpdb->update($wpdb->prefix."invoices", ['email' => ''], ['email' => $email->email]);

            // residential qutoes
            $wpdb->update($wpdb->prefix."quotesheet", ['clientEmail' => ''], ['clientEmail' => $email->email]);

            // commercial quotes
            $wpdb->update($wpdb->prefix."commercial_quotesheet", ['clientEmail' => ''], ['clientEmail' => $email->email]);

            // monthly/quarterly maintenance
            $wpdb->update($wpdb->prefix."maintenance_contract", ['client_email' => ''], ['client_email' => $email->email]);

            // special maintenance 
            $wpdb->update($wpdb->prefix."special_contract", ['client_email' => ''], ['client_email' => $email->email]);

            // commercial maintenance
            $wpdb->update($wpdb->prefix."maintenance_contract", ['client_email' => ''], ['client_email' => $email->email]);

            // leads
            $wpdb->update($wpdb->prefix."leads", ['email' => ''], ['email' => $email->email]);

            // clients database
            $wpdb->update($wpdb->prefix."leads", ['email' => ''], ['email' => $email->email]);

        }
        
        return $this;
    }
}