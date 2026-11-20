<?php

declare(strict_types=1);

namespace Drupal\Tests\custom_banners\Unit;

use Drupal\custom_banners\CourseSearchPills;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\custom_banners\CourseSearchPills
 * @group custom_banners
 */
class CourseSearchPillsTest extends UnitTestCase {

  /**
   * @covers ::resolve
   */
  public function testResolveUsesCanonicalOrderAndAliases(): void {
    $terms = [
      $this->term('TI'),
      $this->term('Marketing'),
      $this->term('Administração'),
      $this->term('Engenharia Civil'),
      $this->term('Enfermagem'),
      $this->term('Design'),
      $this->term('Direito'),
    ];

    $resolved = CourseSearchPills::resolve($terms);

    $this->assertSame([
      'Tecnologia',
      'Marketing',
      'Administração',
      'Engenharia',
      'Saúde',
      'Design',
      'Direito',
    ], array_column($resolved, 'label'));
    $this->assertSame('TI', $resolved[0]['term_label']);
    $this->assertSame('Engenharia Civil', $resolved[3]['term_label']);
    $this->assertSame('Enfermagem', $resolved[4]['term_label']);
  }

  /**
   * @covers ::resolve
   */
  public function testResolveOmitsMissingAreas(): void {
    $resolved = CourseSearchPills::resolve([
      $this->term('Marketing'),
      $this->term('Design Gráfico'),
    ]);

    $this->assertSame([
      ['label' => 'Marketing', 'term_label' => 'Marketing'],
      ['label' => 'Design', 'term_label' => 'Design Gráfico'],
    ], $resolved);
  }

  /**
   * @covers ::resolve
   */
  public function testResolvePrefersExactNameOverPrefix(): void {
    $resolved = CourseSearchPills::resolve([
      $this->term('Tecnologia da Informação'),
      $this->term('Tecnologia'),
    ]);

    $this->assertSame('Tecnologia', $resolved[0]['label']);
    $this->assertSame('Tecnologia', $resolved[0]['term_label']);
  }

  private function term(string $name): object {
    return new class($name) {

      public function __construct(private string $name) {}

      public function label(): string {
        return $this->name;
      }

    };
  }

}
