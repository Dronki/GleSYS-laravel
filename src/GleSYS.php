<?php

namespace Dronki\GleSYS;

class GleSYS {

    protected $aResponse = false;
    protected $oPunyCode = false;

    protected $sAPIUrl = '';
    protected $oCurl = null;

    public function __construct() {
        $this->sAPIUrl = config( 'glesys.api.url' );
    }

    /**
     * Makes an API call to the GleSYS API
     * 
     * @param string $sRequest The API-request
     * @param array $aParams or bool false if no arguments are to be passed.
     * @return bool true on success or false on error.
     */
    protected function request( string $sRequest, array $aParams ) {
        $sRequestUrl = $this->sAPIUrl . $sRequest . '/format/json';

        $this->oCurl = curl_init();
        curl_setopt_array( $this->oCurl, [
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => http_build_query( $aParams ),
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_URL             => $sRequestUrl,
            CURLOPT_TIMEOUT         => config( 'glesys.api.timeout', 30 ),
            CURLOPT_USERAGENT       => 'GleSYS API Client V.0.0.1a',
            CURLOPT_USERPWD         => config( 'glesys.api.user' ) . ':' . config( 'glesys.api.token' ),

            CURLOPT_CUSTOMREQUEST    => 'POST',
            CURLOPT_SSH_AUTH_TYPES  => CURLSSH_AUTH_ANY,
        ] );

        $response = curl_exec( $this->oCurl );

        if( empty( $response ) ) {
            return false;
        }

        $this->aResponse = json_decode( $response, true );
        $this->aResponse = $this->aResponse['response'];
        
        // Check if the code isn't 200
        if( $this->aResponse['status']['code'] != 200 ) {
            $this->aResponse = [
                'code' => $this->aResponse['status']['code'],
                'text' => $this->aResponse['status']['text'],
                'debug' => $this->aResponse['status']['debug'],
            ];
            return false;
        }
        return true;
    }

    public function getResponse() {
        return $this->aResponse;
    }

    public function punyEncode( string $sURL = '' ) {
        if( empty($this->oPunyCode) ) {
            $this->oPunyCode = new idna_convert();
        }
        return $this->oPunyCode->encode($sURL);
    }

    public function punyDecode( string $sURL = '' ) {
        if( empty($this->oPunyCode) ) {
            $this->oPunyCode = new idna_convert();
        }
        return $this->oPunyCode->decode($sURL);
    }
    
}
