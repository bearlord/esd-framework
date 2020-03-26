<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Client;

use ESD\Core\Plugins\Config\ToConfigArray;

class ClientConfig
{
    use ToConfigArray;

    /**
     * Total timeout, including connection, send, and receive all timeouts
     * @var float
     */
    protected $timeout;

    /**
     * Connection timed out
     * @var float
     */
    protected $connectTimeout;

    /**
     * Read time out
     * @var float
     */
    protected $readTimeout;

    /**
     * Write time out
     * @var float
     */
    protected $writeTimeout;

    /**
     * Verify server certificate. After enabling, it will verify whether the certificate corresponds to the host domain name.
     * If not, the connection will be closed automatically.
     * @var bool
     */
    protected $sslVerifyPeer;

    /**
     * SSL allow self-signed
     * @var bool
     */
    protected $sslAllowSelfSigned;

    /**
     * Set the server host name to use with the ssl_verify_peer configuration or Client :: verifyPeerCert.
     * @var string
     */
    protected $sslHostName;

    /**
     * When ssl_verify_peer is set to true, it is used to verify the CA certificate used by the remote certificate.
     * This option is the full path and file name of the CA certificate in the local file system.
     * @var string
     */
    protected $sslCafile;

    /**
     * If ssl_cafile is not set or the file pointed to by ssl_cafile does not exist,
     * the applicable certificate will be searched in the directory specified by ssl_capath.
     * The directory must be a certificate directory that has been hashed.
     * @var string
     */
    protected $sslCapath;

    /**
     * Http proxy host
     * @var array
     */
    protected $httpProxyHost;

    /**
     * Open EOF detection. This option will detect the data sent by the client connection.
     * It will be delivered to the Worker process only when the end of the data packet is the specified string.
     * Otherwise, data packets will be concatenated until the buffer area is exceeded or timeout will not be aborted.
     * When an error occurs, the underlying layer will consider it a malicious connection,
     * discard the data and force the connection to be closed.
     * @var bool
     */
    protected $openEofCheck;

    /**
     * Enable EOF automatic subcontracting.
     * When open_eof_check is set, the underlying detection data is buffered with a specific string at the end,
     * but only the end of the received data is intercepted for comparison by default.
     * At this time, multiple pieces of data may be merged into one package.
     * @var bool
     */
    protected $openEofSplit;

    /**
     * Used with open_eof_check or open_eof_split to set EOF strings.
     * @var string
     */
    protected $packageEof;

    /**
     * turn on the packet length detection feature. Packet length detection provides parsing of the fixed header and body format.
     * After it is enabled, it can be guaranteed that the Worker process onReceive will receive a complete packet each time.
     * @var bool
     */
    protected $openLengthCheck;

    /**
     * Package length type, consistent with pack function. Swoole support 10 types：
     *
     * c: signed, 1 bytes
     * C: unsigned, 1 bytes
     * s: signed, Host byte order, 2 bytes
     * S: unsigned, Host byte order, 2 bytes
     * n: unsigned, network byte order, 2 bytes
     * N: unsigned, network byte order, 4 bytes
     * l: signed, Host byte order, 4 bytes
     * L: unsigned, Host byte order, 4 bytes
     * v: unsigned, little-endian、2 bytes
     * V: unsigned, little-endian、4 bytes
     * @var string
     */
    protected $packageLengthType;

    /**
     * Set the maximum packet size in bytes
     * @var int
     */
    protected $packageMaxLength;

    /**
     * Calculate the length from the few bytes, there are generally two cases:
     * The value of length contains the entire package (header + body), package_body_offset is 0
     * The length of the header is N bytes. The value of length does not include the header, only the body of the package. The package_body_offset is set to N.
     * @var int
     */
    protected $packageBodyOffset;

    /**
     * Which offset of the packet header is the package length value .
     * @var int
     */
    protected $packageLengthOffset;

    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * @param float $timeout
     */
    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return float
     */
    public function getConnectTimeout(): float
    {
        return $this->connectTimeout;
    }

    /**
     * @param float $connectTimeout
     */
    public function setConnectTimeout(float $connectTimeout): void
    {
        $this->connectTimeout = $connectTimeout;
    }

    /**
     * @return float
     */
    public function getReadTimeout(): float
    {
        return $this->readTimeout;
    }

    /**
     * @param float $readTimeout
     */
    public function setReadTimeout(float $readTimeout): void
    {
        $this->readTimeout = $readTimeout;
    }

    /**
     * @return float
     */
    public function getWriteTimeout(): float
    {
        return $this->writeTimeout;
    }

    /**
     * @param float $writeTimeout
     */
    public function setWriteTimeout(float $writeTimeout): void
    {
        $this->writeTimeout = $writeTimeout;
    }

    /**
     * @return bool
     */
    public function isSslVerifyPeer(): bool
    {
        return $this->sslVerifyPeer;
    }

    /**
     * @param bool $sslVerifyPeer
     */
    public function setSslVerifyPeer(bool $sslVerifyPeer): void
    {
        $this->sslVerifyPeer = $sslVerifyPeer;
    }

    /**
     * @return bool
     */
    public function isSslAllowSelfSigned(): bool
    {
        return $this->sslAllowSelfSigned;
    }

    /**
     * @param bool $sslAllowSelfSigned
     */
    public function setSslAllowSelfSigned(bool $sslAllowSelfSigned): void
    {
        $this->sslAllowSelfSigned = $sslAllowSelfSigned;
    }

    /**
     * @return string
     */
    public function getSslHostName(): string
    {
        return $this->sslHostName;
    }

    /**
     * @param string $sslHostName
     */
    public function setSslHostName(string $sslHostName): void
    {
        $this->sslHostName = $sslHostName;
    }

    /**
     * @return string
     */
    public function getSslCafile(): string
    {
        return $this->sslCafile;
    }

    /**
     * @param string $sslCafile
     */
    public function setSslCafile(string $sslCafile): void
    {
        $this->sslCafile = $sslCafile;
    }

    /**
     * @return string
     */
    public function getSslCapath(): string
    {
        return $this->sslCapath;
    }

    /**
     * @param string $sslCapath
     */
    public function setSslCapath(string $sslCapath): void
    {
        $this->sslCapath = $sslCapath;
    }

    /**
     * @return array
     */
    public function getHttpProxyHost(): array
    {
        return $this->httpProxyHost;
    }

    /**
     * @param array $httpProxyHost
     */
    public function setHttpProxyHost(array $httpProxyHost): void
    {
        $this->httpProxyHost = $httpProxyHost;
    }

    /**
     * @return bool
     */
    public function isOpenEofCheck(): bool
    {
        return $this->openEofCheck;
    }

    /**
     * @param bool $openEofCheck
     */
    public function setOpenEofCheck(bool $openEofCheck): void
    {
        $this->openEofCheck = $openEofCheck;
    }

    /**
     * @return bool
     */
    public function isOpenEofSplit(): bool
    {
        return $this->openEofSplit;
    }

    /**
     * @param bool $openEofSplit
     */
    public function setOpenEofSplit(bool $openEofSplit): void
    {
        $this->openEofSplit = $openEofSplit;
    }

    /**
     * @return string
     */
    public function getPackageEof(): string
    {
        return $this->packageEof;
    }

    /**
     * @param string $packageEof
     */
    public function setPackageEof(string $packageEof): void
    {
        $this->packageEof = $packageEof;
    }

    /**
     * @return bool
     */
    public function isOpenLengthCheck(): bool
    {
        return $this->openLengthCheck;
    }

    /**
     * @param bool $openLengthCheck
     */
    public function setOpenLengthCheck(bool $openLengthCheck): void
    {
        $this->openLengthCheck = $openLengthCheck;
    }

    /**
     * @return string
     */
    public function getPackageLengthType(): string
    {
        return $this->packageLengthType;
    }

    /**
     * @param string $packageLengthType
     */
    public function setPackageLengthType(string $packageLengthType): void
    {
        $this->packageLengthType = $packageLengthType;
    }

    /**
     * @return int
     */
    public function getPackageMaxLength(): int
    {
        return $this->packageMaxLength;
    }

    /**
     * @param int $packageMaxLength
     */
    public function setPackageMaxLength(int $packageMaxLength): void
    {
        $this->packageMaxLength = $packageMaxLength;
    }

    /**
     * @return int
     */
    public function getPackageBodyOffset(): int
    {
        return $this->packageBodyOffset;
    }

    /**
     * @param int $packageBodyOffset
     */
    public function setPackageBodyOffset(int $packageBodyOffset): void
    {
        $this->packageBodyOffset = $packageBodyOffset;
    }

    /**
     * @return int
     */
    public function getPackageLengthOffset(): int
    {
        return $this->packageLengthOffset;
    }

    /**
     * @param int $packageLengthOffset
     */
    public function setPackageLengthOffset(int $packageLengthOffset): void
    {
        $this->packageLengthOffset = $packageLengthOffset;
    }

}