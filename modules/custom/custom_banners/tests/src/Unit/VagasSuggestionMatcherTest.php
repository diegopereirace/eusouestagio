<?php

declare(strict_types=1);

namespace Drupal\Tests\custom_banners\Unit;

use Drupal\custom_banners\SearchTextNormalizer;
use Drupal\custom_banners\VagasSuggestionMatcher;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\custom_banners\VagasSuggestionMatcher
 * @group custom_banners
 */
class VagasSuggestionMatcherTest extends UnitTestCase {

  /**
   * @covers ::match
   */
  public function testAccentAndCaseInsensitive(): void {
    $titles = [
      $this->row('Estágio em Marketing'),
      $this->row('Analista de Dados'),
    ];
    $empresas = [
      $this->row('Estágio Corp'),
    ];

    $result = VagasSuggestionMatcher::match('ESTAGIO', $titles, $empresas);
    $this->assertCount(2, $result);
    $this->assertSame('Estágio em Marketing', $result[0]['value']);
    $this->assertSame('vaga', $result[0]['type']);
    $this->assertSame('Estágio Corp', $result[1]['value']);
    $this->assertSame('empresa', $result[1]['type']);
  }

  /**
   * @covers ::match
   */
  public function testPrefixBeforeSubstringAndVagaBeforeEmpresa(): void {
    $titles = [
      $this->row('Marketing Digital'),
      $this->row('Estágio em Marketing'),
    ];
    $empresas = [
      $this->row('Marketing SA'),
    ];

    $result = VagasSuggestionMatcher::match('mark', $titles, $empresas);
    $this->assertSame(
      ['Marketing Digital', 'Marketing SA', 'Estágio em Marketing'],
      array_column($result, 'value'),
    );
    $this->assertSame(['vaga', 'empresa', 'vaga'], array_column($result, 'type'));
  }

  /**
   * @covers ::match
   */
  public function testDedupeAndLimitTen(): void {
    $titles = [];
    for ($i = 1; $i <= 12; $i++) {
      $titles[] = $this->row(sprintf('Estágio %02d', $i));
    }
    // Duplicata lógica (mesmo norm).
    $titles[] = $this->row('Estágio 01');

    $result = VagasSuggestionMatcher::match('estagio', $titles, []);
    $this->assertCount(10, $result);
    $values = array_column($result, 'value');
    $this->assertCount(10, array_unique($values));
  }

  /**
   * @covers ::match
   */
  public function testLiteralWildcardsAndQuotes(): void {
    $titles = [
      $this->row('100% remoto'),
      $this->row('foo_bar'),
      $this->row("O'Reilly stage"),
      $this->row('Estágio comum'),
    ];

    $this->assertSame([], VagasSuggestionMatcher::match('%%', $titles, []));
    $this->assertSame(
      [['value' => '100% remoto', 'label' => '100% remoto', 'type' => 'vaga']],
      VagasSuggestionMatcher::match('100%', $titles, []),
    );
    $this->assertSame(
      [['value' => 'foo_bar', 'label' => 'foo_bar', 'type' => 'vaga']],
      VagasSuggestionMatcher::match('foo_', $titles, []),
    );
    $this->assertSame(
      [['value' => "O'Reilly stage", 'label' => "O'Reilly stage", 'type' => 'vaga']],
      VagasSuggestionMatcher::match("o'reilly", $titles, []),
    );
  }

  /**
   * @covers ::match
   */
  public function testShortQueryReturnsEmpty(): void {
    $titles = [$this->row('Estágio')];
    $this->assertSame([], VagasSuggestionMatcher::match('e', $titles, []));
    $this->assertSame([], VagasSuggestionMatcher::match(' ', $titles, []));
  }

  /**
   * @covers ::match
   */
  public function testQueryTruncatedToEightyChars(): void {
    $needle = str_repeat('a', 80);
    $titles = [
      $this->row(str_repeat('A', 90) . ' fim'),
    ];
    // Com 81 'a's o match falharia se não truncasse; com 80 casa o prefixo.
    $longQ = str_repeat('a', 90);
    $result = VagasSuggestionMatcher::match($longQ, $titles, []);
    $this->assertCount(1, $result);
    $this->assertSame(SearchTextNormalizer::normalize($needle), SearchTextNormalizer::normalize(mb_substr($longQ, 0, 80)));
  }

  /**
   * @return array{name: string, norm: string}
   */
  private function row(string $name): array {
    return [
      'name' => $name,
      'norm' => SearchTextNormalizer::normalize($name),
    ];
  }

}
