<?php

namespace CisTools;

use CisTools\Enum\FileTransferProtocol;
use FTP\Connection;
use RuntimeException;

class FtpHelper
{

    /**
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @param FileTransferProtocol $type
     * @param int $port
     * @param int $timeout
     * @return void
     * @throws RuntimeException: If an error occurs an exception with corresponding message is thrown.
     */
    public static function testConnection(
        string $hostname,
        string $username,
        string $password,
        FileTransferProtocol $type = FileTransferProtocol::FTP,
        int $port = 21,
        int $timeout = 90
    ): void {
        $connection = self::getOpenConnection($hostname, $username, $password, $type, $port, $timeout);

        if ($type === FileTransferProtocol::SFTP) {
            $resource = ssh2_sftp($connection);
            if (!$resource) {
                throw new RuntimeException('Unable to request the SFTP subsystem from the already connected SSH2 server.');
            }
        }

        if ($type === FileTransferProtocol::FTP || $type === FileTransferProtocol::FTPS) {
            ftp_close($connection);
        } else {
            ssh2_disconnect($connection);
        }
    }

    /**
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @param FileTransferProtocol $type
     * @param int $port
     * @param int $timeout
     * @return Connection|resource: For FTP(S) the connection for usage with ftp_* is returned. For SFTP the connection for usage with ssh2_* is returned (you need ssh2_sftp() first if you want to continue with the SFTP subsystem).
     * @throws RuntimeException: If an error occurs an exception with corresponding message is thrown.
     */
    public static function getOpenConnection(
        string $hostname,
        string $username,
        string $password,
        FileTransferProtocol $type = FileTransferProtocol::FTP,
        int $port = 21,
        int $timeout = 90
    ) {
        $connection = match ($type) {
            FileTransferProtocol::FTP => @ftp_connect($hostname, $port, $timeout),
            FileTransferProtocol::FTPS => @ftp_ssl_connect($hostname, $port, $timeout),
            FileTransferProtocol::SFTP => @ssh2_connect($hostname, $port),
            default => throw new RuntimeException('Unknown file transfer protocol.')
        };

        if (false === $connection) {
            throw new RuntimeException('Unable to connect to server.');
        }

        if ($type === FileTransferProtocol::FTP || $type === FileTransferProtocol::FTPS) {
            $loggedIn = @ftp_login($connection, $username, $password);
        } else {
            $loggedIn = @ssh2_auth_password($connection, $username, $password);
        }

        if ($loggedIn !== true) {
            throw new RuntimeException('Unable to log in via ' . $type->name);
        }

        return $connection;
    }
}