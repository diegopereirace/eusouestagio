<?php

declare(strict_types=1);

namespace Drupal\Tests\custom_banners\Unit;

use Drupal\custom_banners\SearchTextNormalizer;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\custom_banners\SearchTextNormalizer
 * @group custom_banners
 */
class SearchTextNormalizerTest extends UnitTestCase {

  /**
   * @covers ::normalize
   * @dataProvider providerNormalize
   */
  public function testNormalize(string $input, string $expected): void {
    $this->assertSame($expected, SearchTextNormalizer::normalize($input));
  }

  /**
   * @return array<string, array{0: string, 1: string}>
   */
  public static function providerNormalize(): array {
    return [
      'accents and case' => ['São Paulo', 'sao paulo'],
      'uppercase fortaleza' => ['FORTALEZA', 'fortaleza'],
      'trim' => ['  Administração  ', 'administracao'],
      'empty' => ['   ', ''],
      'ti short' => ['TI', 'ti'],
    ];
  }

  /**
   * @covers ::citySuggestionLabel
   * @dataProvider providerCityLabel
   */
  public function testCitySuggestionLabel(string $cidade, string $estado, int $count, string $expected): void {
    $this->assertSame(
      $expected,
      SearchTextNormalizer::citySuggestionLabel($cidade, $estado, $count),
    );
  }

  /**
   * @return array<string, array{0: string, 1: string, 2: int, 3: string}>
   */
  public static function providerCityLabel(): array {
    return [
      'contract fortaleza' => ['Fortaleza', 'CE', 12, 'Fortaleza - CE - (12)'],
      'contract sao paulo' => ['São Paulo', 'SP', 48, 'São Paulo - SP - (48)'],
      'single vacancy' => ['Recife', 'PE', 1, 'Recife - PE - (1)'],
      'zero clamped display' => ['Natal', 'RN', 0, 'Natal - RN - (0)'],
    ];
  }

  /**
   * @covers ::matchesNormalizedPrefix
   * @dataProvider providerPrefixMatch
   */
  public function testMatchesNormalizedPrefix(string $haystack, string $needle, bool $expected): void {
    $this->assertSame(
      $expected,
      SearchTextNormalizer::matchesNormalizedPrefix($haystack, $needle),
    );
  }

  /**
   * @return array<string, array{0: string, 1: string, 2: bool}>
   */
  public static function providerPrefixMatch(): array {
    return [
      'exact course' => ['administracao', 'administracao', TRUE],
      'prefix adm' => ['administracao', 'adm', TRUE],
      'ti exact' => ['ti', 'ti', TRUE],
      'ti does not match notificacao' => ['notificacao', 'ti', FALSE],
      'ti does not match marketing' => ['marketing', 'ti', FALSE],
      'empty needle' => ['ti', '', FALSE],
      'empty haystack' => ['', 'ti', FALSE],
      'cidade prefix' => ['sao paulo', 'sao', TRUE],
      'cidade exact' => ['sao paulo', 'sao paulo', TRUE],
      'cidade mid-substring rejected' => ['belo horizonte', 'horizon', FALSE],
    ];
  }

}
