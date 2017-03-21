<?php
namespace Libraries;

class Database {
    
    private $conn = null;

	public function __construct($file="/../configuration.php") 
	{
		if (!$cfg = parse_ini_file($file, TRUE)) 
			throw new \exception('Unable to open '.$file.'.');

        $dsn = $cfg['database']['driver'].
        	   ':host='.$cfg['database']['host'] .
               ((!empty($cfg['database']['port'])) ? (';port='.$cfg['database']['port']): '').
               ';dbname=' . $cfg['database']['schema'].";charset=utf8";
		
		/*
		$param = [
		    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
		    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		    \PDO::ATTR_EMULATE_PREPARES   => false,
			];
         */

		try {
			$this->db = new \PDO($dsn, $cfg['database']['username'],$cfg['database']['password']); //, $param);
		}catch(\PDOException $e){
            die($e->getMessage());
        }
	}

    
	public function runSQL($sql)
	{
		try {
			$stmt = $this->db->query($sql);
			return (is_object($stmt))? $stmt->fetchAll(\PDO::FETCH_OBJ):$stmt;
		} catch(\PDOException $e) {
			return 'Caught exception: '.$e->getMessage()."\n";
		}
	}

    public function insertSQL($table,$objData)
    {
    	###return is_object($objData)? "":"";

    	if (is_array($objData)) {
			$fields = array_keys($objData);
			$values = array_values($objData);
			$sql = "INSERT INTO ".$table." (".implode(', ', $fields).") VALUES ('".implode("', '", $values)."')";
			return $this->db->query($sql);
		}else {
			return null;
		}
    }

    public function insertSQLOBJ($table,$objData)
    {
    	###return is_object($objData)? "":"";

    	if (is_array($objData)) {
			$fields = array_keys($objData);
			$values = array_values($objData);
			$sql = "INSERT INTO ".$table." (".implode(', ', $fields).") VALUES ('".implode("', '", $values)."')";
			return $this->db->query($sql);
		}else {
			return null;
		}
    }

    public function updateSQL($table, $array, $where)
    {
    	if (is_array($array) && is_array($where)) {
			foreach ($array as $key => $val) {
				$valstr[] = $key . " = '". $val ."'";
			}
			$sql = "UPDATE ".$table." SET ".implode(', ', $valstr);
			$sql .= " WHERE ".$this->_where($where);
			return $this->db->query($sql);
			
		}else {
			return "null";
		}																				
    }

    public function countRowSQL($sql)
    {

    }

    /*
    * PRIVATE CLASS
    */
    private function _where( $where, $andor = 'AND' )
	{
		if( is_array( $where ) ) {
			$w = array();
			foreach ( $where as $col => $val ) {
				$equal = '=';
				$not = false;
				/* like:
				 * 	array( '!key' => 'value' )
				 * 	produces sql query with
				 * 	key NOT 'value'
				 */
				if( strpos( $col, '!' ) !== false && strpos( $col, '!' ) == 0 ) {
					$col = trim( str_replace( '!', null, $col ) );
					$not = true;
				}
				/* current means get previous query */
				if( ( string ) $val == '@CURRENT' ) {
					$n = $not ? 'NOT' : null;
					$val = $this->db->getQuery();
					$w[] = " ( {$col} {$n} IN ( {$val} ) ) ";
				}
				/* see SPDb#valid() */
				elseif( $col == '@VALID' ) {
					$col = '';
					$w[] = $val;
				}
				elseif( is_numeric( $col ) ) {
					$w[] = $this->escape( $val );
				}
				/* like:
				 * 	array( 'key' => array( 'from' => 1, 'to' => 10 ) )
				 * 	produces sql query with
				 * 	key BETWEEN 1 AND 10
				 */
				elseif( is_array( $val ) && ( isset( $val[ 'from' ] ) || isset( $val[ 'to' ] ) ) ) {
					if( ( isset( $val[ 'from' ] ) && isset( $val[ 'to' ] ) ) && $val[ 'from' ] != SPC::NO_VALUE && $val[ 'to' ] != SPC::NO_VALUE ) {
						$val[ 'to' ] = $this->escape( $val[ 'to' ] );
						$val[ 'from' ] = $this->escape( $val[ 'from' ] );
						$w[] = " ( {$col} BETWEEN {$val[ 'from' ]} AND {$val[ 'to' ]} ) ";
					}
					elseif( $val[ 'from' ] != SPC::NO_VALUE && $val[ 'to' ] == SPC::NO_VALUE ) {
						$val[ 'from' ] = $this->escape( $val[ 'from' ] );
						$w[] = " ( {$col} > {$val[ 'from' ]} ) ";
					}
					elseif( $val[ 'from' ] == SPC::NO_VALUE && $val[ 'to' ] != SPC::NO_VALUE ) {
						$val[ 'to' ] = $this->escape( $val[ 'to' ] );
						$w[] = " ( {$col} < {$val[ 'to' ]} ) ";
					}

				}
				/* like:
				 * 	array( 'key' => array( 1,2,3,4 ) )
				 * 	produces sql query with
				 * 	key IN ( 1,2,3,4 )
				 */
				elseif( is_array( $val ) ) {
					$v = array();
					foreach ( $val as $i => $k ) {						;
						if( strlen( $k ) ) {
							$k = $this->escape( $k );
							$v[] = "'{$k}'";
						}
					}
					$val = implode( ',', $v );
					$n = $not ? 'NOT' : null;
					$w[] = " ( {$col} {$n} IN ( {$val} ) ) ";
				}
				else {
					/* changes the equal sign */
					$n = $not ? '!' : null;
					/* is lower */
					if( strpos( $col, '<' ) ) {
						$equal = '<';
						$col = trim( str_replace( '<', null, $col ) );
					}
					/* is greater */
					elseif( strpos( $col, '>' ) ) {
						$equal = '>';
						$col = trim( str_replace( '>', null, $col ) );
					}
					 /* is like */
					elseif( strpos( $val, '%' ) !== false  ) {
						$equal = 'LIKE';
					}
					/* regular expressions handling
					 * array( 'key' => 'REGEXP:^search$' )
					 */
					elseif( strpos( $val, 'REGEXP:' ) !== false  ) {
						$equal = 'REGEXP';
						$val = str_replace( 'REGEXP:', null, $val );
					}
					elseif( strpos( $val, 'RLIKE:' ) !== false  ) {
						$equal = 'RLIKE';
						$val = str_replace( 'RLIKE:', null, $val );
					}
					/* ^^ regular expressions handling ^^ */

					/* SQL functions within the query
					 * array( 'created' => 'FUNCTION:NOW()' )
					 */
					if( strstr( $val, 'FUNCTION:' ) ) {
						$val = str_replace( 'FUNCTION:', null, $val );
					}
					else {
						$val = $this->escape( $val );
						$val = "'{$val}'";
					}
					$w[] = " ( {$col} {$n}{$equal}{$val} ) ";
				}
			}
			$where = implode( " {$andor} ", $w );
		}
		return $where;
	}

} /* END Class Database */