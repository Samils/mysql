<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\MySQLAdapter\Entity\Actions
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
namespace Sammy\Packs\MySQLAdapter\Entity\Actions {
  use Sammy\Packs\Kery\MySQL as Kery;
  use PDOException;
  /**
   * Make sure the module base internal trait is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists ('Sammy\Packs\MySQLAdapter\Entity\Actions\Update')) {
  /**
   * @trait Update
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
  trait Update {
    /**
     * @method mixed update
     *
     * Run a 'update' query in the used
     * database.
     *
     * On a given model the get the specified
     * datas.
     *
     * @param string $model
     *
     *  The model name.
     *
     * @param array $datas
     *
     *  Datas and a list of rules
     *  to get them.
     *
     * @return mixed
     */
    function update ($model = '', $datas = array ()) {
      if (!(is_string ($model) && !empty ($model)))
        return;

      $model = strtolower ($model);
      $datas = is_array ($datas) ? $datas : [];

      $query = Kery::Update ($model, ['bind' => false], $datas);

      #echo "\nQuery => $query\nEndQuery\n\n";

      $this->useDatabase ();

      $datas = Kery::ReadQuery ($datas);
      $query = $this->conn->prepare ($query);

      try {
        return $query->execute (array_values ($datas ['@filter']));
      } catch (PDOException $errorObject) {
        self::QueryError ($errorObject);
      }
    }
  }}
}
