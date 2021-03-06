<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generics;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\FileTypeMapper;

/**
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Trait_>
 */
class TraitTemplateTypeRule implements Rule
{

	private \PHPStan\Type\FileTypeMapper $fileTypeMapper;

	private \PHPStan\Rules\Generics\TemplateTypeCheck $templateTypeCheck;

	public function __construct(
		FileTypeMapper $fileTypeMapper,
		TemplateTypeCheck $templateTypeCheck
	)
	{
		$this->fileTypeMapper = $fileTypeMapper;
		$this->templateTypeCheck = $templateTypeCheck;
	}

	public function getNodeType(): string
	{
		return Node\Stmt\Trait_::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$docComment = $node->getDocComment();
		if ($docComment === null) {
			return [];
		}

		if (!isset($node->namespacedName)) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$traitName = (string) $node->namespacedName;
		$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
			$scope->getFile(),
			$traitName,
			null,
			null,
			$docComment->getText()
		);

		return $this->templateTypeCheck->check(
			$node,
			$resolvedPhpDoc->getTemplateTags(),
			sprintf('PHPDoc tag @template for trait %s cannot have existing class %%s as its name.', $traitName),
			sprintf('PHPDoc tag @template for trait %s cannot have existing type alias %%s as its name.', $traitName),
			sprintf('PHPDoc tag @template %%s for trait %s has invalid bound type %%s.', $traitName),
			sprintf('PHPDoc tag @template %%s for trait %s with bound type %%s is not supported.', $traitName)
		);
	}

}
