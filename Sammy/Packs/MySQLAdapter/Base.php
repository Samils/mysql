<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\MySQLAdapter
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
namespace Sammy\Packs\MySQLAdapter {
  use Sammy\Packs\KlassProps;
  use PDO as PhpDataObject;
  use PDOException;
  /**
   * Make sure the module base internal trait is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists ('Sammy\Packs\MySQLAdapter\Base')) {
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
    use Table;
    use Entity;
    use KlassProps;
    use QueryError;

    /**
     * @var array $props
     */
    protected static $props = [
      'db' => '',
      'user' => '',
      'host' => '',
      'port' => null
    ];

    protected function construct () {
      if (!(is_string ($this->database) && $this->database)) {
        $this->database = $this->db;
      }
    }

    /**
     * @method object|array connect
     *
     * Connect the database and create
     * the connection object.
     *
     * @param array $connectionDatas
     *
     * Connection datas that should be used
     * to connect the database, including the
     * database name.
     *
     * This should not be an empty array, on
     * condition that the connection depends
     * on the whole the given datas from it.
     *
     * @return object|array
     */
    public function connect ($connectionDatas = []) {
      if (is_array ($connectionDatas)) {
        $this->setProp ($connectionDatas);
      }

      $format = requires ('format');

      $this->setPort ();

      try {

        $this->conn = new PhpDataObject (
          $format->format ('mysql:host={}{};db_name={}',
            $this->host,
            $this->port,
            $this->db
          ),
          $this->user,
          $this->pass
        );

        $useDBQuery = $format->format ('use {}' , $this->db);

        $createDBQuery = $format->format (join (' ', [
          'create database if not exists {}',
          'default character set utf8 default',
          'collate utf8_general_ci'
        ]) , $this->db);

        $this->conn->exec ($createDBQuery);
        $this->conn->exec ($useDBQuery);

        $this->conn->setAttribute (
          PhpDataObject::ATTR_ERRMODE,
          PhpDataObject::ERRMODE_EXCEPTION
        );

        $this->conn->setAttribute (
          PhpDataObject::ATTR_DEFAULT_FETCH_MODE,
          PhpDataObject::FETCH_OBJ
        );

        # Return connection data object
        return $this->conn;
      } catch (PDOException $errorObject) {
        return [
          'status' => 0,
          'port' => $this->port,
          'errorObject' => $errorObject
        ];
      }
    }

    protected function setPort () {
      $port = trim ($this->port);

      $this->port = '';

      if (is_numeric ($port)) {
        $this->port = join ('', [':', $port]);
      }
    }

    protected function useDatabase ($databaseName = null) {
      if (!(is_string ($databaseName) && $databaseName)) {
        $databaseName = $this->db;
      }

      try {
        $this->conn->exec (join (' ', ['use', $databaseName]));
      } catch (PDOException $errorObject) {
        self::QueryError ($errorObject);
      }
    }

    protected function query ($query, $databaseName = null) {
      $this->useDatabase ($databaseName);

      try {
        return $this->conn->query ($query);
      } catch (PDOException $errorObject) {
        self::QueryError ($errorObject);
      }
    }
  }}
}
