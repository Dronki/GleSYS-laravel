<?php

namespace Dronki\GleSYS;

use Dronki\GleSYS\Support\EmailAccount;

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
    protected function request( string $sRequest, array $aParams = [] ) {
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

    /**
     * Get the list of all domains and email accounts and aliases
     *
     * @return array on success, false on error.
     */
    public function emailOverview() {
        $bSuccess = $this->request( 'email/overview' );
        if( $bSuccess ) {
            $aResponse = [
                'status' => $this->aResponse['status'],
                'summary' => $this->aResponse['overview']['summary'],
                'domains' => $this->aReponse['overview']['domains'] ?? null,
                'debug' => $this->aResponse['debug'] ?? null,
            ];
            $this->aResponse = $aResponse;
            return $this->aResponse;
        }
        return false;
    }

    /**
     * Gets a list of all accounts and aliases of a domain with full details.
     * Passing an optional filter will only return the filtered accounts.
     * The filter must be a valid email address.
     * Example: user@example.com
     *
     * @param string $sDomain required.
     * @param string $sFilter optional.
     * @param bool $bToObject optional, if true the returned array will be an array of object. (See EmailAccount class)
     * @return array on success, false on error.
     */
    public function emailsByDomain( $sDomain, $sFilter = '', $bToObject = false ) {
        $aParams = [
            'domainname' => $this->punyEncode( $sDomain ),
        ];

        if( !empty($sFilter) ) {
            $aParams['filter'] = $sFilter;
        }
        $bSuccess = $this->request( 'email/list', $aParams );
        if( $bSuccess ) {
            if( $bToObject ) {
                $aCollection = [];
                $aAliases = $this->aResponse['list']['emailaliases'];
                foreach( $this->aResponse['list']['emailaccounts'] as $aEmail ) {
                    extract( $aEmail );
                    $oEmail = new EmailAccount( $emailaccount,
                        array_pop( explode( '@', $emailaccount ) ),
                        [],
                        $antispamlevel,
                        $antivirus,
                        $rejectspam,
                        $autorespond,
                        $autorespondmessage,
                        $autorespondsaveemail,
                        $quota['max'],
                        $created,
                        $modified,
                    );
                    if( !empty($aAliases) ) {
                        $aAccountAliases = [];
                        foreach( $aAliases as $aAlias ) {
                            if( $aAlias['goto'] == $emailaccount ) {
                                $aAccountAliases[] = $aAlias['emailalias'];
                            }
                        }
                        $oEmail->aliases = $aAccountAliases;
                    }
                    $aCollection[] = $oEmail;
                }
                return $aCollection;
            }
            $aResponse = [
                'status' => $this->aResponse['status'],
                'accounts' => $this->aResponse['list']['emailaccounts'] ?? null,
                'aliases' => $this->aResponse['list']['emailaliases'] ?? null,
                'debug' => $this->aResponse['debug'] ?? null,
            ];
            $this->aResponse = $aResponse;
            return $this->aResponse;
        }
        return false;
    }

    /**
     * Create an email account.
     * The domain must be created first.
     * The email account must not exist.
     * The email account must be a valid email address.
     * The password must be at least 6 characters long.
     * 
     * @param string $sEmailAccount required.
     * @param string $sPassword required.
     * @param array $aData optional, used to set the accounts antispamlevel and such properties.
     * @return array on success, false on error.
     */
    public function emailCreateAccount( $sEmailAccount, $sPassword, $aData = [] ) {
        $oEmail = new EmailAccount( $sEmailAccount );
        $oEmail->password = $sPassword;

        $aParams = $oEmail->toArray();

        if( !empty($aData) ) {
            $aParams = array_merge( $aParams, $aData );
        }

        $bSuccess = $this->request( 'email/createaccount', $aParams );
        if( !$bSuccess ) {
            return false;
        }
        return $this->aResponse;
    }

    /**
     * Edit an email and change things like quota, password, etc.
     * Allowed values for antispam is 0-5. Quota is in MB.
     *
     * @param string $sEmail required.
     * @param array $aData, parts are optional array(
     *  'antispamlevel' => 0-5,
     *  'antivirus' => 0|1,
     *  'rejectspam' => 0|1,
     *  'autorespond' => 0|1,
     *  'autorespondmessage' => string,
     *  'autorespondsaveemail' => 0|1,
     *  'quota' => new quota in MB,
     *  'password' => new password,
     * )
     * @return array on success, false on error.
     */
    public function emailEditAccount( $sEmailAccount, $aData = [] ) {
        $aParams = [
            'emailaccount' => $sEmailAccount,
        ];
        if( !empty($aData) ) {
            $aParams = array_merge( $aParams, $aData );
        }

        $bSuccess = $this->request( 'email/editaccount', $aParams );
        if( !$bSuccess ) {
            return false;
        }
        return $this->aResponse;
    }

    /**
     * Delete an email-account or alias.
     * 
     * @param string $sEmailAccount required.
     * @return array on success, false on error.
     */
    public function emailDeleteAccount( $sEmailAccount ) {
        $aParams = [
            'emailaccount' => $sEmailAccount,
        ];
        $bSuccess = $this->request( 'email/delete', $aParams );
        if( !$bSuccess ) {
            return false;
        }
        return $this->aResponse;
    }

    /**
     * Get information about the quota of an account.
     * I.e how much space is used and how much is left.
     * 
     * @param string $sEmailAccount required.
     * @return array on success, false on error.
     */
    public function emailAccountQuota( $sEmailAccount ) {
        $aParams = [
            'emailaccount' => $sEmailAccount,
        ];
        $bSuccess = $this->request( 'email/quota', $aParams );
        if( !$bSuccess ) {
            return false;
        }
        return $this->aResponse;
    }

    /**
     * Create an email-alias
     * 
     * @param string $sAlias required.
     * @param string|array $sGoto required.
     * @return array on success, false on error.
     */
    public function emailCreateAlias( $sAlias, $sGoto ) {
        if( is_array( $sGoto ) ) {
            $sGoto = implode( ',', $sGoto );
        }
        $aParams = [
            'emailalias' => $sAlias,
            'goto' => $sGoto,
        ];
        $bSuccess = $this->request( 'email/createalias', $aParams );
        if( !$bSuccess ) {
            return false;
        }
        return $this->aResponse;
    }

    /**
     * Edit an email-alias
     * 
     * @param string $sAlias required.
     * @param string|array $sGoto required.
     * @return array on success, false on error.
     */
    public function emailEditAlias( $sAlias, $sGoto ) {
        if( is_array( $sGoto ) ) {
            $sGoto = implode( ',', $sGoto );
        }
        $aParams = [
            'emailalias' => $sAlias,
            'goto' => $sGoto,
        ];
        $bSuccess = $this->request( 'email/editalias', $aParams );
        if( !$bSuccess ) {
            return false;
        }
        return $this->aResponse;
    }

    /**
     * Delete an email-alias
     * 
     * @param string $sAlias required.
     * @return array on success, false on error.
     */
    public function emailDeleteAlias( $sAlias ) {
        $aParams = [
            'emailaccount' => $sAlias,
        ];
        $bSuccess = $this->request( 'email/delete', $aParams );
        if( !$bSuccess ) {
            return false;
        }
        return $this->aResponse;
    }

}
