<?php

namespace App\Message;

final readonly class AnalyzeProtocolMessage {
    public function __construct(public int $protocolId)
    {}
}