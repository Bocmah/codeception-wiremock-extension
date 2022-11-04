<?php
/**
 * This file is part of codeception-wiremock-extension.
 *
 * codeception-wiremock-extension is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * codeception-wiremock-extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with codeception-wiremock-extension.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Codeception\Extension;

/**
 * Manages the current running WireMock process.
 */
class WireMockProcess
{
    /**
     * WireMock server log.
     */
    public const LOG_FILE_NAME = 'wiremock.out';

    /**
     * @var resource
     */
    private $process;

    /**
     * @var resource[]
     */
    private array $pipes;

    /**
     * Starts a wiremock process.
     *
     * @throws \Exception
     */
    public function start(string $jarPath, string $logsPath, string $arguments): void
    {
        $this->checkIfProcessIsRunning();

        echo "Running " . $this->getCommandPrefix() . "java -jar {$jarPath}{$arguments}";

        $this->process = proc_open(
            $this->getCommandPrefix() . "java -jar {$jarPath}{$arguments}",
            $this->createProcessDescriptors($logsPath),
            $this->pipes,
            null,
            null,
            ['bypass_shell' => true]
        );

        $this->checkProcessIsRunning();
    }

    private function createProcessDescriptors(string $logsPath): array
    {
        $logFile = $logsPath . DIRECTORY_SEPARATOR . self::LOG_FILE_NAME;

        return [
            ['pipe', 'r'],
            ['file', $logFile, 'w'],
            ['file', $logFile, 'a'],
        ];
    }

    /**
     * @throws \Exception
     */
    private function checkIfProcessIsRunning(): void
    {
        if ($this->process !== null) {
            throw new \Exception('The server is already running');
        }
    }

    public function isRunning(): bool
    {
        return isset($this->process) && is_resource($this->process);
    }

    /**
     * @throws \Exception
     */
    private function checkProcessIsRunning(): void
    {
        if (!$this->isRunning()) {
            throw new \Exception('Could not start local wiremock server');
        }
    }

    /**
     * Stops the process.
     */
    public function stop(): void
    {
        if (is_resource($this->process)) {
            foreach ($this->pipes as $pipe) {
                if (is_resource($pipe)) {
                    fflush($pipe);
                    fclose($pipe);
                }
            }
            proc_close($this->process);
            unset($this->process);
        }
    }

    private function getCommandPrefix(): string
    {
        if (PHP_OS === 'WIN32' || PHP_OS === 'WINNT' || PHP_OS === 'Windows') {
            return '';
        }

        return 'exec ';
    }
}
