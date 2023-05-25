<?php declare(strict_types=1);

namespace ESD\Nikic\PhpParser\Lexer\TokenEmulator;

/** @internal */
interface TokenEmulatorInterface
{
    public function isEmulationNeeded(string $code): bool;

    /**
     * @return array Modified Tokens
     */
    public function emulate(string $code, array $tokens): array;
}
