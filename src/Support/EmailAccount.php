<?php

namespace Dronki\GleSYS\Support;

class EmailAccount {

    protected $aliases = [];
    protected $domain = "";
    protected $email = "";

    protected $antiSpamLevel = 0;
    protected $antivirus = "yes";
    protected $rejectSpam = "yes";

    protected $autoRespond = "no";
    protected $autoRespondMessage = "";
    protected $autoResponseSaveEmail = "yes";

    protected $quota = 0;

    protected $created = "";
    protected $modified = "";

    protected $password = "";

    public function __construct( $email, $domain = "", $aliases = [], $antiSpamLevel = 0, $antivirus = "yes", $rejectSpam = "yes", $autoRespond = "no", $autoRespondMessage = "", $autoResponseSaveEmail = "yes", $quota = 0, $created = "", $modified = "" ) {
        $this->email = $email;
        $this->domain = $domain;
        $this->aliases = $aliases;
        $this->antiSpamLevel = $antiSpamLevel;
        $this->antivirus = $antivirus;
        $this->rejectSpam = $rejectSpam;
        $this->autoRespond = $autoRespond;
        $this->autoRespondMessage = $autoRespondMessage;
        $this->autoResponseSaveEmail = $autoResponseSaveEmail;
        $this->quota = $quota;
        $this->created = $created;
        $this->modified = $modified;
    }

    // Magic setter
    public function __set( $name, $value ) {
        $this->$name = $value;
    }

    // Magic getter
    public function __get( $name ) {
        return $this->$name;
    }

    // Class to array
    public function toArray() {
        return [
            'email' => $this->email,
            'domain' => $this->domain,
            'aliases' => $this->aliases,
            'antispamlevel' => $this->antiSpamLevel,
            'antivirus' => $this->antivirus,
            'rejectspam' => $this->rejectSpam,
            'autorespond' => $this->autoRespond,
            'autorespondmessage' => $this->autoRespondMessage,
            'autorespondsaveemail' => $this->autoRespondeSaveEmail,
            'quota' => $this->quota,
            'created' => $this->created,
            'modified' => $this->modified,
        ];
    }

    public function toSerializedObject() {
        return serialize( $this->toArray() );
    }

    public function toJson() {
        return json_encode( $this->toArray() );
    }

}
