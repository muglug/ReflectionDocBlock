<?php declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2018 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;

/**
 * Reflection class for a {@}deprecated tag in a Docblock.
 */
final class Deprecated extends BaseTag implements Factory\StaticMethod
{
    protected $name = 'deprecated';

    /** @var Description|null Description of the tag. */
    protected $description;

    /**
     * PCRE regular expression matching a version vector.
     * Assumes the "x" modifier.
     */
    public const REGEX_VECTOR = '(?:
        # Normal release vectors.
        \d\S*
        |
        # VCS version vectors. Per PHPCS, they are expected to
        # follow the form of the VCS name, followed by ":", followed
        # by the version vector itself.
        # By convention, popular VCSes like CVS, SVN and GIT use "$"
        # around the actual version vector.
        [^\s\:]+\:\s*\$[^\$]+\$
    )';

    /** @var string The version vector. */
    private $version = '';

    public function __construct($version = null, ?Description $description = null)
    {
        Assert::nullOrStringNotEmpty($version);

        $this->version = $version;
        $this->description = $description;
    }

    /**
     * @return static
     */
    public static function create(
        ?string $body,
        ?DescriptionFactory $descriptionFactory = null,
        ?TypeContext $context = null
    ) {
        if (empty($body)) {
            return new static();
        }

        $matches = [];
        if (!preg_match('/^(' . self::REGEX_VECTOR . ')\s*(.+)?$/sux', $body, $matches)) {
            return new static(
                null,
                null !== $descriptionFactory ? $descriptionFactory->create($body, $context) : null
            );
        }

        if (!$descriptionFactory) {
            throw new \UnexpectedValueException('$descriptionFactory should not be null');
        }

        return new static(
            $matches[1],
            $descriptionFactory->create($matches[2] ?? '', $context)
        );
    }

    /**
     * Gets the version section of the tag.
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * Returns a string representation for this tag.
     */
    public function __toString(): string
    {
        return $this->version . ($this->description ? ' ' . $this->description->render() : '');
    }
}
