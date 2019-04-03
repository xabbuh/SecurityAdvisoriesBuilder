<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types=1);

namespace RoaveTest\SecurityAdvisories;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Roave\SecurityAdvisories\Boundary;
use Roave\SecurityAdvisories\Version;
use function Safe\preg_match;
use function strpos;

/**
 * Tests for {@see \Roave\SecurityAdvisories\Boundary}
 *
 * @covers \Roave\SecurityAdvisories\Boundary
 */
final class BoundaryTest extends TestCase
{
    /**
     * @dataProvider invalidBoundaryStrings
     */
    public function testRejectsInvalidBoundaryStrings(string $boundaryString) : void
    {
        $this->expectException(InvalidArgumentException::class);

        Boundary::fromString($boundaryString);
    }

    /**
     * @dataProvider validBoundaryStrings
     */
    public function testValidBoundaryString(string $boundaryString, string $expectedNormalizedString) : void
    {
        $boundary = Boundary::fromString($boundaryString);

        self::assertSame($expectedNormalizedString, $boundary->getBoundaryString());
        self::assertEquals($boundary, Boundary::fromString($boundary->getBoundaryString()));
    }

    /**
     * @dataProvider validBoundaryStrings
     */
    public function testLimitIncluded(string $boundaryString) : void
    {
        self::assertSame(
            strpos($boundaryString, '=') !== false,
            Boundary::fromString($boundaryString)->limitIncluded()
        );
    }

    /**
     * @dataProvider validBoundaryStrings
     */
    public function testGetVersion(string $boundaryString) : void
    {
        // fixme: to use here regexp from other class
        preg_match('/((?:\d+\.)*\d+)[._-]?(?:(stable|beta|b|rc|alpha|a|patch|p)[._-]?((?:\d+\.)*\d+)?)?\s*$/', $boundaryString, $matches);

        self::assertTrue(
            Version::fromString($matches[1])->equalTo(Boundary::fromString($boundaryString)->getVersion())
        );
    }

    /**
     * @dataProvider validBoundaryStrings
     */
    public function testBoundaryNotAdjacentToItself(string $boundaryString) : void
    {
        self::assertFalse(Boundary::fromString($boundaryString)->adjacentTo(Boundary::fromString($boundaryString)));
    }

    /**
     * @dataProvider adjacentBoundaries
     */
    public function testAdjacentBoundaries(string $boundary1String, string $boundary2String) : void
    {
        $boundary1 = Boundary::fromString($boundary1String);
        $boundary2 = Boundary::fromString($boundary2String);

        self::assertTrue($boundary1->adjacentTo($boundary2));
        self::assertTrue($boundary2->adjacentTo($boundary1));
    }

    /**
     * @dataProvider nonAdjacentBoundaries
     */
    public function testNonAdjacentBoundaries(string $boundary1String, string $boundary2String) : void
    {
        $boundary1 = Boundary::fromString($boundary1String);
        $boundary2 = Boundary::fromString($boundary2String);

        self::assertFalse($boundary1->adjacentTo($boundary2));
        self::assertFalse($boundary2->adjacentTo($boundary1));
    }

    /**
     * @return string[][]
     */
    public function invalidBoundaryStrings() : array
    {
        return [
            [''],
            ['foo'],
            ['1'],
            ['1.2.3'],
            ['1.2.3='],
            ['1.2.3<='],
            ['1.2.3<'],
            ['1.2.3>'],
            ['1.2.3>='],
            ['<'],
            ['>'],
            ['<='],
            ['>='],
            ['='],
            ['=='],
            ['><'],
            ['<>'],
            ['=>'],
            ['=<'],
            ['=>1.2'],
            ['=<1.2'],
            ['1.2'],
            ['beta'],
            ['>beta'],
            ['<beta'],
            ['<=beta'],
            ['>=beta'],
        ];
    }

    /**
     * @return string[][]
     */
    public function validBoundaryStrings() : array
    {
        return [
            ['>1.2.3', '>1.2.3'],
            ['>=1.2.3', '>=1.2.3'],
            ['=1.2.3', '=1.2.3'],
            ['<=1.2.3', '<=1.2.3'],
            ['<1.2.3', '<1.2.3'],
            ['>1.2.3.0', '>1.2.3'],
            ['>=1.2.3.0', '>=1.2.3'],
            ['=1.2.3.0', '=1.2.3'],
            ['<=1.2.3.0', '<=1.2.3'],
            ['<1.2.3.0', '<1.2.3'],
            ['>1.0', '>1'],
            ['>=1.0', '>=1'],
            ['=1.0', '=1'],
            ['<=1.0', '<=1'],
            ['<1.0', '<1'],
            ['>  1.2.3', '>1.2.3'],
            ['>=  1.2.3', '>=1.2.3'],
            ['=  1.2.3', '=1.2.3'],
            ['<=  1.2.3', '<=1.2.3'],
            ['<  1.2.3', '<1.2.3'],
            ['  >  1.2.3   ', '>1.2.3'],
            ['  >=  1.2.3   ', '>=1.2.3'],
            ['  =  1.2.3   ', '=1.2.3'],
            ['  <=  1.2.3   ', '<=1.2.3'],
            ['  <  1.2.3   ', '<1.2.3'],
            ['>1.2.3-beta', '>1.2.3-beta'],
            ['>=1.2.3-beta', '>=1.2.3-beta'],
            ['=1.2.3-beta', '=1.2.3-beta'],
            ['<=1.2.3-beta', '<=1.2.3-beta'],
            ['<1.2.3-beta', '<1.2.3-beta'],
            ['>1.2.3-beta', '>1.2.3-beta'],
            ['>=1.2.3-beta', '>=1.2.3-beta'],
            ['=1.2.3-beta', '=1.2.3-beta'],
            ['<=1.2.3-beta', '<=1.2.3-beta'],
            ['<1.2.3-beta', '<1.2.3-beta'],
            ['>  1.2.3-patch', '>1.2.3-patch'],
            ['>=  1.2.3-patch', '>=1.2.3-patch'],
            ['=  1.2.3-patch', '=1.2.3-patch'],
            ['<=  1.2.3-patch', '<=1.2.3-patch'],
            ['<  1.2.3-patch', '<1.2.3-patch'],
            ['  >  1.2.3-patch   ', '>1.2.3-patch'],
            ['  >=  1.2.3-patch   ', '>=1.2.3-patch'],
            ['  =  1.2.3-patch   ', '=1.2.3-patch'],
            ['  <=  1.2.3-patch   ', '<=1.2.3-patch'],
            ['  <  1.2.3-patch   ', '<1.2.3-patch'],
        ];
    }

    /**
     * @return string[][]
     */
    public function adjacentBoundaries() : array
    {
        return [
            ['<1', '=1'],
            ['<1', '>=1'],
            ['<=1', '>1'],
            ['=1', '>1'],
            ['<1-alpha.1', '=1-alpha.1'],
            ['<1-alpha.1', '>=1-alpha.1'],
            ['<=1-alpha.1', '>1-alpha.1'],
            ['=1-alpha.1', '>1-alpha.1'],
        ];
    }

    /**
     * @return string[][]
     */
    public function nonAdjacentBoundaries() : array
    {
        return [
            ['<1', '<1'],
            ['<1', '<=1'],
            ['<=1', '<=1'],
            ['<=1', '>=1'],
            ['=1', '=1'],
            ['=1', '<=1'],
            ['=1', '>=1'],
            ['<1', '=1.1'],
            ['<1', '>=1.1'],
            ['<=1', '>1.1'],
            ['=1', '>1.1'],
            ['<1-beta.1.1', '<1-beta.1.1'],
            ['<1-beta.1.1', '<=1-beta.1.1'],
            ['<=1-beta.1.1', '<=1-beta.1.1'],
            ['<=1-beta.1.1', '>=1-beta.1.1'],
            ['=1-beta.1.1', '=1-beta.1.1'],
            ['=1-beta.1.1', '<=1-beta.1.1'],
            ['=1-beta.1.1', '>=1-beta.1.1'],
            ['<1-beta.1.1', '=1.1-beta.1.1'],
            ['<1-beta.1.1', '>=1.1-beta.1.1'],
            ['<=1-beta.1.1', '>1.1-beta.1.1'],
            ['=1-beta.1.1', '>1.1-beta.1.1'],
        ];
    }
}
