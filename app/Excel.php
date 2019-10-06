<?php

namespace App;

class Excel
{
    private $mysql_connect;
    private $excel_file;

    function __construct($connection, $filename)
    {

        if (!class_exists(self::class)) {

            throw new \Exception("Excel library required!");
        }

        $this->mysql_connect = $connection;
        $this->excel_file = $filename;
    }

    public
    function excel_to_mysql_by_index($table_name, $index = 0, $columns_names = 0, $start_row_index = false, $condition_functions = false, $transform_functions = false, $unique_column_for_update = false, $table_types = false, $table_keys = false, $table_encoding = "utf8_general_ci", $table_engine = "InnoDB") {

        $PHPExcel_file = \PHPExcel_IOFactory::load($this->excel_file);

        $PHPExcel_file->setActiveSheetIndex($index);
        return $this->excel_to_mysql($PHPExcel_file->getActiveSheet(), $table_name, $columns_names, $start_row_index, $condition_functions, $transform_functions, $unique_column_for_update, $table_types, $table_keys, $table_encoding, $table_engine);
    }

    private
    function excel_to_mysql($worksheet, $table_name, $columns_names, $start_row_index, $condition_functions, $transform_functions, $unique_column_for_update, $table_types, $table_keys, $table_encoding, $table_engine)
    {

        if (!$this->mysql_connect->connect_error) {

            // Строка для названий столбцов таблицы MySQL
            $columns = array();
            // Количество столбцов на листе Excel
            $columns_count = \PHPExcel_Cell::columnIndexFromString($worksheet->getHighestColumn());

            // Если в качестве имен столбцов передан массив, то проверяем соответствие его длинны с количеством столбцов
            if ($columns_names) {
                if (is_array($columns_names)) {
                    $columns_names_count = count($columns_names);
                    if ($columns_names_count < $columns_count) {
                        return false;
                    } elseif ($columns_names_count > $columns_count) {
                        $columns_count = $columns_names_count;
                    }
                } else {
                    return false;
                }
            }

            // Если указаны типы столбцов
            if ($table_types) {
                if (is_array($table_types)) {
                    // Проверяем количество столбцов и типов
                    if (count($table_types) != count($columns_names)) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
            $table_name = "`{$table_name}`";

            // Проверяем, что $columns_names - массив и $unique_column_for_update находиться в его пределах
            if ($unique_column_for_update) {
                $unique_column_for_update = is_array($columns_names) ? ($unique_column_for_update <= count($columns_names) ? "`{$columns_names[$unique_column_for_update - 1]}`" : false) : false;
            }

            // Перебираем столбцы листа Excel и генерируем строку с именами через запятую
            for ($column = 0; $column < $columns_count; $column++) {
                $column_name = (is_array($columns_names) ? $columns_names[$column] : ($columns_names == 0 ? "column{$column}" : $worksheet->getCellByColumnAndRow($column, $columns_names)->getValue()));
                $columns[] = "`{$column_name}`";
            }

            $query_string = "DROP TABLE IF EXISTS {$table_name}";

            if (defined("EXCEL_MYSQL_DEBUG")) {
                if (EXCEL_MYSQL_DEBUG) {
                    var_dump($query_string);
                }
            }

            // Удаляем таблицу MySQL, если она существовала (если не указан столбец с уникальным значением для обновления)
            if ($unique_column_for_update ? true : $this->mysql_connect->query($query_string)) {
                $columns_types = $ignore_columns = array();
                // Обходим столбцы и присваиваем типы
                foreach ($columns as $index => $value) {
                    if ($value != "``") {
                        if ($table_types) {
                            $columns_types[] = $value . " " . $table_types[$index];
                        } else {
                            $columns_types[] = $value . " TEXT NOT NULL";
                        }
                    } else {
                        $ignore_columns[] = $index;
                        unset($columns[$index]);
                    }
                }
                // Если указаны ключевые поля, то создаем массив ключей
                if ($table_keys) {
                    $columns_keys = array();
                    foreach ($table_keys as $key => $value) {
                        $columns_keys[] = "{$value} (`{$key}`)";
                    }
                    $columns_keys_list = implode(", ", $columns_keys);
                    $columns_keys = ", {$columns_keys_list}";
                } else {
                    $columns_keys = null;
                }
                $columns_types_list = implode(", ", $columns_types);
                $query_string = "CREATE TABLE IF NOT EXISTS {$table_name} ({$columns_types_list}{$columns_keys}) COLLATE = '{$table_encoding}' ENGINE = {$table_engine}";
                if (defined("EXCEL_MYSQL_DEBUG")) {
                    if (EXCEL_MYSQL_DEBUG) {
                        var_dump($query_string);
                    }
                }
                // Создаем таблицу MySQL
                if ($this->mysql_connect->query($query_string)) {
                    // Коллекция значений уникального столбца для удаления несуществующих строк в файле импорта (используется при обновлении)
                    $id_list_in_import = array();
                    // Количество строк на листе Excel
                    $rows_count = $worksheet->getHighestRow();
                    // Получаем массив всех объединенных ячеек
                    $all_merged_cells = $worksheet->getMergeCells();
                    // Перебираем строки листа Excel
                    for ($row = ($start_row_index ? $start_row_index : (is_array($columns_names) ? 1 : $columns_names + 1)); $row <= $rows_count; $row++) {
                        // Строка со значениями всех столбцов в строке листа Excel
                        $values = array();
                        // Перебираем столбцы листа Excel
                        for ($column = 0; $column < $columns_count; $column++) {
                            if (in_array($column, $ignore_columns)) {
                                continue;
                            }
                            // Строка со значением объединенных ячеек листа Excel
                            $merged_value = null;
                            // Ячейка листа Excel
                            $cell = $worksheet->getCellByColumnAndRow($column, $row);
                            // Перебираем массив объединенных ячеек листа Excel
                            foreach ($all_merged_cells as $merged_cells) {
                                // Если текущая ячейка - объединенная,
                                if ($cell->isInRange($merged_cells)) {
                                    // то вычисляем значение первой объединенной ячейки, и используем её в качестве значения текущей ячейки
                                    $merged_value = explode(":", $merged_cells);
                                    $merged_value = $worksheet->getCell($merged_value[0])->getValue();
                                    break;
                                }
                            }
                            // Проверяем, что ячейка не объединенная: если нет, то берем ее значение, иначе значение первой объединенной ячейки
                            $value = strlen($merged_value) == 0 ? $cell->getValue() : $merged_value;
                            // Если задан массив функций с условиями
                            if ($condition_functions) {
                                if (isset($condition_functions[$columns_names[$column]])) {
                                    // Проверяем условие
                                    if (!$condition_functions[$columns_names[$column]]($value)) {
                                        break;
                                    }
                                }
                            }
                            $value = $transform_functions ? (isset($transform_functions[$columns_names[$column]]) ? $transform_functions[$columns_names[$column]]($value) : $value) : $value;
                            $values[] = "'{$this->mysql_connect->real_escape_string($value)}'";
                        }
                        // Если количество столбцов не равно количеству значений, значит строка не прошла проверку
                        if ($columns_count - count($ignore_columns) != count($values)) {
                            continue;
                        }
                        // Добавляем или проверяем обновлять ли значение
                        $add_to_table = $unique_column_for_update ? false : true;
                        // Если обновляем
                        if ($unique_column_for_update) {
                            // Объединяем массивы для простоты работы
                            $columns_values = array_combine($columns, $values);
                            // Сохраняем уникальное значение
                            $id_list_in_import[] = $columns_values[$unique_column_for_update];
                            // Создаем условие выборки
                            $where = " WHERE {$unique_column_for_update} = {$columns_values[$unique_column_for_update]}";
                            // Удаляем столбец выборки
                            unset($columns_values[$unique_column_for_update]);
                            $query_string = "SELECT COUNT(*) AS count FROM {$table_name}{$where}";
                            if (defined("EXCEL_MYSQL_DEBUG")) {
                                if (EXCEL_MYSQL_DEBUG) {
                                    var_dump($query_string);
                                }
                            }
                            // Проверяем есть ли запись в таблице
                            $count = $this->mysql_connect->query($query_string);
                            $count = $count->fetch_assoc();
                            // Если есть, то создаем запрос и обновляем
                            if (intval($count['count']) != 0) {
                                $set = array();
                                foreach ($columns_values as $column => $value) {
                                    $set[] = "{$column} = {$value}";
                                }
                                $set_list = implode(", ", $set);
                                $query_string = "UPDATE {$table_name} SET {$set_list}{$where}";
                                if (defined("EXCEL_MYSQL_DEBUG")) {
                                    if (EXCEL_MYSQL_DEBUG) {
                                        var_dump($query_string);
                                    }
                                }
                                if (!$this->mysql_connect->query($query_string)) {
                                    return false;
                                }
                            } else {
                                $add_to_table = true;
                            }
                        }
                        // Добавляем строку в таблицу MySQL
                        if ($add_to_table) {
                            $columns_list = implode(", ", $columns);
                            $values_list = implode(", ", $values);
                            $query_string = "INSERT INTO {$table_name} ({$columns_list}) VALUES ({$values_list})";
                            if (defined("EXCEL_MYSQL_DEBUG")) {
                                if (EXCEL_MYSQL_DEBUG) {
                                    var_dump($query_string);
                                }
                            }
                            if (!$this->mysql_connect->query($query_string)) {
                                return false;
                            }
                        }
                    }
                    if (!empty($id_list_in_import)) {
                        $id_list = implode(", ", $id_list_in_import);
                        $query_string = "DELETE FROM {$table_name} WHERE {$unique_column_for_update} NOT IN ({$id_list})";
                        if (defined("EXCEL_MYSQL_DEBUG")) {
                            if (EXCEL_MYSQL_DEBUG) {
                                var_dump($query_string);
                            }
                        }
                        $this->mysql_connect->query($query_string);
                    }
                    return true;
                }
            }
        }
        return false;
    }


}