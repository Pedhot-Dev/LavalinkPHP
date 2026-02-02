<?php

declare(strict_types=1);

namespace PedhotDev\LavalinkPHP\Attribute;

use Attribute;

/**
 * Attribute used to mark a dependency for injection.
 * While NepotismFree-DI primarily uses constructor injection,
 * this can be used for documentation or by custom reflection tools.
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final class Inject
{
    public function __construct(
        public readonly ?string $serviceId = null
    ) {}
}
