<?php

namespace Blocktrail\SDK;

use Blocktrail\SDK\Bitcoin\BIP32Key;
use Blocktrail\SDK\Bitcoin\BIP32Path;

/**
 * Interface Wallet
 */
interface WalletInterface {

    const FEE_STRATEGY_BASE_FEE = 'base_fee';
    const FEE_STRATEGY_OPTIMAL = 'optimal';
    const FEE_STRATEGY_LOW_PRIORITY = 'low_priority';

    /**
     * @param BlocktrailSDKInterface        $sdk                        SDK instance used to do requests
     * @param string                        $identifier                 identifier of the wallet
     * @param string                        $primaryMnemonic
     * @param array[string, string]         $primaryPublicKeys
     * @param array[string, string]         $backupPublicKey            should be BIP32 master public key M/
     * @param array[array[string, string]]  $blocktrailPublicKeys
     * @param int                           $keyIndex
     * @param string                        $network
     * @param bool                          $testnet
     * @param string                        $checksum
     */
    public function __construct(BlocktrailSDKInterface $sdk, $identifier, $primaryMnemonic, $primaryPublicKey, $backupPublicKey, $blocktrailPublicKeys, $keyIndex, $network, $testnet, $checksum);

    /**
     * return the wallet identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * return the wallet primary mnemonic (for backup purposes)
     *
     * @return string
     */
    public function getPrimaryMnemonic();

    /**
     * return list of Blocktrail co-sign extended public keys
     *
     * @return array[]      [ [xpub, path] ]
     */
    public function getBlocktrailPublicKeys();

    /**
     * unlock wallet so it can be used for payments
     *
     * @param          $options ['primary_private_key' => key] OR ['passphrase' => pass]
     * @param callable $fn
     * @return bool
     */
    public function unlock($options, callable $fn = null);

    /**
     * lock the wallet (unsets primary private key)
     *
     * @return void
     */
    public function lock();

    /**
     * check if wallet is locked
     *
     * @return bool
     */
    public function isLocked();

    /**
     * upgrade wallet to different blocktrail cosign key
     *
     * @param $keyIndex
     * @throws \Exception
     * @return bool
     */
    public function upgradeKeyIndex($keyIndex);

    /**
     * get address for the specified path
     *
     * @param string|BIP32Path  $path
     * @return string
     */
    public function getAddressByPath($path);

    /**
     * get address and redeemScript for specified path
     *
     * @param string    $path
     * @return array[string, string]     [address, redeemScript]
     */
    public function getRedeemScriptByPath($path);

    /**
     * get the path (and redeemScript) to specified address
     *
     * @param string $address
     * @return array
     */
    public function getPathForAddress($address);

    /**
     * @param string|BIP32Path  $path
     * @return BIP32Key
     * @throws \Exception
     */
    public function getBlocktrailPublicKey($path);

    /**
     * generate a new derived key and return the new path and address for it
     *
     * @return string[]     [path, address]
     */
    public function getNewAddressPair();

    /**
     * generate a new derived private key and return the new address for it
     *
     * @return string
     */
    public function getNewAddress();

    /**
     * get the balance for the wallet
     *
     * @return int[]            [confirmed, unconfirmed]
     */
    public function getBalance();

    /**
     * do wallet discovery (slow)
     *
     * @param int   $gap        the gap setting to use for discovery
     * @return int[]            [confirmed, unconfirmed]
     */
    public function doDiscovery($gap = 200);

    /**
     * create, sign and send a transaction
     *
     * @param array    $outputs             [address => value, ] or [[address, value], ] or [['address' => address, 'value' => value], ] coins to send
     *                                      value should be INT
     * @param string   $changeAddress       change address to use (autogenerated if NULL)
     * @param bool     $allowZeroConf
     * @param bool     $randomizeChangeIdx  randomize the location of the change (for increased privacy / anonimity)
     * @param string   $feeStrategy
     * @param null|int $forceFee            set a fixed fee instead of automatically calculating the correct fee, not recommended!
     * @return string the txid / transaction hash
     */
    public function pay(array $outputs, $changeAddress = null, $allowZeroConf = false, $randomizeChangeIdx = true, $feeStrategy = self::FEE_STRATEGY_OPTIMAL, $forceFee = null);

    /**
     * build inputs and outputs lists for TransactionBuilder
     *
     * @param TransactionBuilder $txBuilder
     * @return array
     * @throws \Exception
     */
    public function buildTx(TransactionBuilder $txBuilder);

    /**
     * create, sign and send transction based on TransactionBuilder
     *
     * @param TransactionBuilder $txBuilder
     * @param bool $apiCheckFee     let the API check if the fee is correct
     * @return string
     */
    public function sendTx(TransactionBuilder $txBuilder, $apiCheckFee = true);

    /**
     * use the API to get the best inputs to use based on the outputs
     *
     * @param array[]   $outputs
     * @param bool      $lockUTXO
     * @param bool      $allowZeroConf
     * @param null|int  $forceFee
     * @return array
     */
    public function coinSelection($outputs, $lockUTXO = true, $allowZeroConf = false, $forceFee = null);

    /**
     * @return int
     */
    public function getOptimalFeePerKB();

    /**
     * @param TransactionBuilder    $txBuilder
     * @param bool|true             $lockUTXOs
     * @param bool|false            $allowZeroConf
     * @param null|int              $forceFee
     * @return TransactionBuilder
     */
    public function coinSelectionForTxBuilder(TransactionBuilder $txBuilder, $lockUTXOs = true, $allowZeroConf = false, $forceFee = null);

    /**
     * delete the wallet
     *
     * @param bool $force       ignore warnings (such as non-zero balance)
     * @return mixed
     */
    public function deleteWallet($force = false);

    /**
     * setup a webhook for our wallet
     *
     * @param string    $url            URL to receive webhook events
     * @param string    $identifier     identifier for the webhook, defaults to WALLET-{$this->identifier}
     * @return array
     */
    public function setupWebhook($url, $identifier = null);

    /**
     * @param string    $identifier     identifier for the webhook, defaults to WALLET-{$this->identifier}
     * @return mixed
     */
    public function deleteWebhook($identifier = null);

    /**
     * get all transactions for the wallet (paginated)
     *
     * @param  integer $page    pagination: page number
     * @param  integer $limit   pagination: records per page (max 500)
     * @param  string  $sortDir pagination: sort direction (asc|desc)
     * @return array            associative array containing the response
     */
    public function transactions($page = 1, $limit = 20, $sortDir = 'asc');

    /**
     * get all addresses for the wallet (paginated)
     *
     * @param  integer $page    pagination: page number
     * @param  integer $limit   pagination: records per page (max 500)
     * @param  string  $sortDir pagination: sort direction (asc|desc)
     * @return array            associative array containing the response
     */
    public function addresses($page = 1, $limit = 20, $sortDir = 'asc');

    /**
     * get all UTXOs for the wallet (paginated)
     *
     * @param  integer $page    pagination: page number
     * @param  integer $limit   pagination: records per page (max 500)
     * @param  string  $sortDir pagination: sort direction (asc|desc)
     * @return array            associative array containing the response
     */
    public function utxos($page = 1, $limit = 20, $sortDir = 'asc');
}
