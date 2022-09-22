<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\MySQLAdapter\Table
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
namespace Sammy\Packs\MySQLAdapter\Table {
  use PDO as DataObject;
  use PDOException as E;
  /**
   * Make sure the module base internal trait is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists ('Sammy\Packs\MySQLAdapter\Table\Base')) {
  /**
   * @trait Base
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
  trait Base {

    /**
     *
     */
    public function dropTable ($table) {

      if (!(is_object($this->conn) && ($this->conn instanceof DataObject)))
        return;

      $this->conn->exec (
        'drop table if exists `'.$table.'`'
      );

      #echo "log: $table dropped\n\n";
    }
    /**
     * [model description]
     * @param  string $table     [description]
     * @param boolean $forceSync [<description>]
     * @param  array  $cols [description]
     * @return [type]            [description]
     */
    public function model ($table = '', $forceSync = false, $cols = []) {
      if (!(is_object ($this->conn) && ($this->conn instanceof DataObject))) {
        return;
      }

      $forceSync = is_bool ($forceSync) ? $forceSync : false;

      $format = requires ('format');

      if (!(is_array($cols) && is_string($table) && $table)) {
        return null;
      }

      $this->useDatabase ();

      $forceSyncStr = $forceSync ? '' : ' if not exists ';

      if ($forceSync) {
        #echo "\n\nTable => $table\n\n";
        try {
          $this->conn->exec (
            $format->format ('drop table if exists `{}`', $table)
          );
        } catch (E $e) {
          exit ("Error => \n\n" . $e->getMessage ());
        }
      }

      $createTableQuery = join (', ', [
        'create table{} `{}` (id int(11) not null AUTO_INCREMENT primary key',
        '`key` varchar (25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , UNIQUE `key` (`key`(25))',
        'createdat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'updatedat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ''
      ]);

      $createTableQuery = $format->format (
        $createTableQuery,
        $forceSyncStr,
        $table
      );

      # Map the '$cols' array in order getting
      # whole the col names and structures.
      # Do this in order creating a query for
      # each each collumn in the table, to create
      # whole at the end of the loop.
      foreach ($cols as $colName => $colStructure) {

        ######## print_r ($colStructure);
        #
        # Having the table name in '$colName' var
        # considere it as the start of the sql command
        # to add it into the current table,
        # now, set its configuration properties in order
        # having the defined datas inside the current collumn
        # in the loop.
        # store the command body inside the '$col_str' variable
        # and fill it according to each property contaings the
        # '$colStructure' array.
        $col_str = '`' . $colName . '`';

        $constraints = [];
        # Now, make a loop around the '$colStructure' array
        # in order getting each property of the current column
        # to configure it for the current table.
        # Inside the '$colStructure' array, its suposed
        # to have the 'propName' and the 'propValue' wich'll
        # be used to know what sort of configuration's being
        # done and process that correctly.
        foreach ($colStructure as $prop => $value) {
          # Remove the '@' char at the begining
          # of the '$prop' string in order avoiding
          # to have the '@type' property like it is
          # in the '$colStructure' array.
          $prop = preg_replace ('/^@+/', '', $prop);
          # 'methName' is the method name
          # that'll rewrite the current property
          # into sql in order filling the '$col_str'
          # string and configure the current column.
          $methName = ('md_prop_rewrite_' . $prop);

          if (method_exists ($this, $methName)) {
            $rewritenProp = call_user_func_array (
              [$this, $methName], [$value, $colStructure]
            );

            if (is_string ($rewritenProp)) {
              $col_str .= $rewritenProp;
            } elseif (is_array ($rewritenProp)) {
              $constraints = array_merge ($constraints, $rewritenProp);
            }
          }
        }

        # map the constraints array and ...
        foreach ($constraints as $constraint) {
          $constraint = $format->format ($constraint, $colName);
          $col_str .= join ('', [', ', $constraint]);
        }

        # Concat the '$col_str' at the end
        # of '$createTableQuery'.
        $createTableQuery .= $col_str . ', ';
      }

      $md_query_end = (
        ') ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;'
      );

      $createTableQuery = preg_replace('/(\s*,\s*)$/', $md_query_end,
        $createTableQuery
      );

      #return;
      try {
        $this->conn->exec ($createTableQuery);
      } catch (E $e) {
        echo "\n\n\n", $createTableQuery, "\n\n\n";
        exit ($e->getMessage());
      }
    }
  }}
}
