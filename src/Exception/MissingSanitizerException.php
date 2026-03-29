<?php

declare(strict_types=1);

namespace Duon\Boiler\Exception;

final class MissingSanitizerException extends RuntimeException implements TemplateException {}
