<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Component;

use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\AbstractFieldFunctionalTest;
use EasyCorp\Bundle\EasyAdminBundle\Twig\Component\Option\Size;

/**
 * Rendering tests for the <twig:ea:Button> component.
 */
class ButtonTest extends AbstractFieldFunctionalTest
{
    public function testDefaultSizeEmitsNoSizeClass(): void
    {
        $html = $this->renderButton('<twig:ea:Button>Click</twig:ea:Button>');

        self::assertStringContainsString('class="btn btn-secondary"', $html);
        self::assertStringNotContainsString('btn-sm', $html);
        self::assertStringNotContainsString('btn-md', $html);
    }

    public function testSmallSizeAsString(): void
    {
        self::assertStringContainsString(
            'class="btn btn-secondary btn-sm"',
            $this->renderButton('<twig:ea:Button size="sm">Click</twig:ea:Button>')
        );
    }

    public function testLargeSizeAsString(): void
    {
        self::assertStringContainsString(
            'class="btn btn-secondary btn-lg"',
            $this->renderButton('<twig:ea:Button size="lg">Click</twig:ea:Button>')
        );
    }

    public function testSmallSizeAsEnum(): void
    {
        self::assertStringContainsString(
            'class="btn btn-secondary btn-sm"',
            $this->renderButton('<twig:ea:Button size="{{ size }}">Click</twig:ea:Button>', ['size' => Size::Small])
        );
    }

    public function testMediumSizeAsEnumEmitsNoSizeClass(): void
    {
        self::assertStringContainsString(
            'class="btn btn-secondary"',
            $this->renderButton('<twig:ea:Button size="{{ size }}">Click</twig:ea:Button>', ['size' => Size::Medium])
        );
    }

    private function renderButton(string $template, array $context = []): string
    {
        return static::getContainer()->get('twig')->createTemplate($template)->render($context);
    }
}
