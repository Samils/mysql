<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\MySQLAdapter\Table\Property
 * - Autoload, application dependencies
 *
 * MIT License
 *
 * Copyright (c) 2020 Ysare
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace Sammy\Packs\MySQLAdapter\Table\Property {
  /**
   * Make sure the module base internal trait is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists ('Sammy\Packs\MySQLAdapter\Table\Property\SQLRewriting')) {
  /**
   * @trait SQLRewriting
   * Base internal trait for the
   * MySQLAdapter module.
   * -
   * This is (in the ils environment)
   * an instance of the php module,
   * wich should contain the module
   * core functionalities that should
   * be extended.
   * -
   * For extending the module, just create
   * an 'exts' directory in the module directory
   * and boot it by using the ils directory boot.
   * -
   */
  trait SQLRewriting {
    /**
     * [md_prop_rewrite_type description]
     * @param  string $val [description]
     * @return string       [description]
     */
    private function md_prop_rewrite_ai ($val = null) {
      return is_bool($val) && $val ? ' AUTO_INCREMENT PRIMARY_KEY ' : '';
    }

    /**
     * [md_prop_rewrite_type description]
     * @param  string $val [description]
     * @return string       [description]
     */
    private function md_prop_rewrite_auto_increment ($val = null) {
      return is_bool($val) && $val ? ' AUTO_INCREMENT PRIMARY_KEY ' : '';
    }

    /**
     * [md_prop_rewrite_type description]
     * @param  string $val [description]
     * @return string       [description]
     */
    private function md_prop_rewrite_comment ($val = null) {
      $val = preg_replace('/\`/', '\\`', \str($val));
      return ' COMMENT \''.$val.'\' ';
    }

    /**
     * [md_prop_rewrite_type description]
     * @param  string $val [description]
     * @return string       [description]
     */
    private function md_prop_rewrite_default ($val = null) {
      $val = preg_replace('/\`/', '\\`', \str($val));
      return ' DEFAULT \''.$val.'\'  ';
    }

    /**
     * [md_prop_rewrite_type description]
     * @param  string $val [description]
     * @return string       [description]
     */
    private function md_prop_rewrite_null ($val = '') {
      return is_bool($val) && $val ? ' NULL  ' : ' NOT NULL  ';
    }

    /**
     * [md_prop_rewrite_type description]
     * @param  string $type [description]
     * @return string       [description]
     */
    private function md_prop_rewrite_type ($type = '') {
      return (' ' . \str ($type) . ' ');
    }

    private function md_prop_rewrite_reference ($reference, $rest = []) {
      $reference = self::readReference ($reference);

      list ($primaryKey, $referenceTable) = $reference;
      list ($onDelete, $onUpdate) = ['', ''];

      if (is_array ($rest) &&
        isset ($rest ['onUpdate']) &&
        self::validReferenceConstrains ($rest ['onUpdate'])) {
        $onUpdate = ' ON UPDATE ' . strtoupper ($rest ['onUpdate']);
      }

      if (is_array ($rest) &&
        isset ($rest ['onDelete']) &&
        self::validReferenceConstrains ($rest ['onDelete'])) {
        $onDelete = ' ON DELETE ' . strtoupper ($rest ['onDelete']);
      }

      $constraintStr = "CONSTRAINT FOREIGN key ({}) REFERENCES {$referenceTable}({$primaryKey}){$onUpdate}{$onDelete}";

      return [$constraintStr];
    }

    private static function readReference ($reference) {
      if (is_string ($reference) && $reference) {
        $references = preg_split ('/\./', $reference);

        if (count ($references) >= 2) {
          return [$references [1], $references [0]];
        }
      } elseif (is_array ($reference) &&
        isset ($reference [0]) &&
        is_string ($reference [0]) &&
        isset ($reference ['inTable']) &&
        is_string ($reference ['inTable'])) {
        return [$reference [0], $reference ['inTable']];
      }
    }

    private static function referenceValue ($reference) {
      $references = self::readReference ($reference);

      list ($primaryKey, $referenceTable) = $references;

      return array_stringify ([$primaryKey, 'inTable' => $referenceTable]);
    }

    private static function validReferenceConstrains ($constraint) {
      return (boolean)(
        is_string ($constraint) &&
        !empty ($constraint)
      );
    }
  }}
}
