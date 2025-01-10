<?php

namespace Brickhouse\View\Attributes;

use Brickhouse\View\Attribute;
use Brickhouse\View\AttributeArgument;
use Brickhouse\View\CompilerContext;
use Brickhouse\View\Node;

class ConditionalAttributes implements Attribute
{
    /**
     * @inheritDoc
     */
    public function attributes(): array
    {
        return [
            'v-if' => AttributeArgument::REQUIRED,
            'v-else-if' => AttributeArgument::REQUIRED,
            'v-else' => AttributeArgument::NONE,
        ];
    }

    /**
     * @inheritDoc
     */
    public function __invoke(CompilerContext $context, string $attribute, null|string $value): string
    {
        // Determine whether this node is the last branch in the conditional.
        // If so, we should close the branch. Otherwise, leave it open.
        $shouldClose = $this->shouldCloseConditional($context->node);

        // @phpstan-ignore match.unhandled
        [$prologue, $epilogue] = match ($attribute) {
            'v-if' => [
                "<?php if(({$value}) ?? false): ?>",
                "<?php endif; ?>",
            ],
            'v-else-if' => [
                "<?php elseif(({$value}) ?? false): ?>",
                "<?php endif; ?>",
            ],
            'v-else' => [
                "<?php else: ?>",
                "<?php endif; ?>",
            ],
        };

        if (!$shouldClose) {
            $epilogue = "";
        }

        return implode([
            $prologue,
            self::BODY_MARKER,
            $epilogue
        ]);
    }

    protected function shouldCloseConditional(Node $node): bool
    {
        $nextSibling = $node->nextSibling;
        if (!$nextSibling) {
            return true;
        }

        $continuingAttributes = [
            'v-else-if',
            'v-else',
        ];

        foreach (array_keys($nextSibling->attributes) as $attribute) {
            if (in_array($attribute, $continuingAttributes)) {
                return false;
            }
        }

        return true;
    }
}