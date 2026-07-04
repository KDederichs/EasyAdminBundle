<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Component;

use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\AbstractFieldFunctionalTest;
use EasyCorp\Bundle\EasyAdminBundle\Twig\Component\Option\Size;

class SwitchTest extends AbstractFieldFunctionalTest
{
    public function testDefaultSizeEmitsNoSizeClass(): void
    {
        $html = $this->renderSwitch('<twig:ea:Switch />');

        self::assertStringContainsString('class="ea-switch"', $html);
        self::assertStringNotContainsString('ea-switch-sm', $html);
        self::assertStringNotContainsString('ea-switch-md', $html);
    }

    public function testSmallSizeAsString(): void
    {
        self::assertStringContainsString(
            'class="ea-switch ea-switch-sm"',
            $this->renderSwitch('<twig:ea:Switch size="sm" />')
        );
    }

    public function testSmallSizeAsEnum(): void
    {
        self::assertStringContainsString(
            'class="ea-switch ea-switch-sm"',
            $this->renderSwitch('<twig:ea:Switch size="{{ size }}" />', ['size' => Size::Small])
        );
    }

    public function testMediumSizeAsEnumEmitsNoSizeClass(): void
    {
        self::assertStringContainsString(
            'class="ea-switch"',
            $this->renderSwitch('<twig:ea:Switch size="{{ size }}" />', ['size' => Size::Medium])
        );
    }

    private function renderSwitch(string $template, array $context = []): string
    {
        return static::getContainer()->get('twig')->createTemplate($template)->render($context);
    }
}
