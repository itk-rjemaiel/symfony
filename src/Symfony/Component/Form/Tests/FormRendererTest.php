<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormRenderer;

class FormRendererTest extends TestCase
{
    public function testHumanize()
    {
        $renderer = $this->getMockBuilder(FormRenderer::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->assertEquals('Is active', $renderer->humanize('is_active'));
        $this->assertEquals('Is active', $renderer->humanize('isActive'));
    }
}
