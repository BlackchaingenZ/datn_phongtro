<?php
if (!defined('_INCODE')) die('Access Deined...');

function query($sql, $data = [], $statementStatus = false)
{
    global $conn;
    $query = false;
    try {
        $statement = $conn->prepare($sql);

        if (empty($data)) {
            $query = $statement->execute();
        } else {
            $query = $statement->execute($data);
        }
    } catch (Exception $exception) {

        require_once 'modules/errors/database.php'; //Import error
        die(); //Dừng hết chương trình
    }

    if ($statementStatus && $query) {
        return $statement;
    }

    return $query;
}


function insert($table, $dataInsert)
{

    $keyArr = array_keys($dataInsert);
    $fieldStr = implode(', ', $keyArr);
    $valueStr = ':' . implode(', :', $keyArr);

    $sql = 'INSERT INTO ' . $table . '(' . $fieldStr . ') VALUES(' . $valueStr . ')';

    return query($sql, $dataInsert);
}

function insertMutiple($table, $dataInsert)
{
    if (is_array($dataInsert) && count($dataInsert) > 0) {
        $keyArr = array_keys($dataInsert[0]);
        $fieldStr = implode(', ', $keyArr);

        // Tạo placeholder cho nhiều bản ghi
        $placeholders = [];
        foreach ($dataInsert as $record) {
            $placeholders[] = '(' . implode(', ', array_fill(0, count($keyArr), '?')) . ')';
        }
        $valueStr = implode(', ', $placeholders);

        // Xây dựng câu lệnh SQL
        $sql = 'INSERT INTO ' . $table . ' (' . $fieldStr . ') VALUES ' . $valueStr;

        // Phẳng mảng dữ liệu để thực hiện truy vấn
        $params = [];
        foreach ($dataInsert as $record) {
            $params = array_merge($params, array_values($record));
        }

        // Thực hiện truy vấn
        return query($sql, $params);
    }
    return false; // Trả về false nếu dữ liệu không hợp lệ
}


function update($table, $dataUpdate, $condition = '')
{

    $updateStr = '';
    foreach ($dataUpdate as $key => $value) {
        $updateStr .= $key . '=:' . $key . ', ';
    }

    $updateStr = rtrim($updateStr, ', ');

    if (!empty($condition)) {
        $sql = 'UPDATE ' . $table . ' SET ' . $updateStr . ' WHERE ' . $condition;
    } else {
        $sql = 'UPDATE ' . $table . ' SET ' . $updateStr;
    }

    return query($sql, $dataUpdate);
}


function delete($table, $condition = '')
{
    if (!empty($condition)) {
        $sql = "DELETE FROM $table WHERE $condition";
    } else {
        $sql = "DELETE FROM $table";
    }

    return query($sql);
}

function deleteEquipment($roomId, $equipmentId)
{
    global $pdo; // Giả sử bạn đang sử dụng PDO

    // Tạo câu lệnh SQL để xóa
    $sql = "DELETE FROM equipment_room WHERE room_id = :room_id AND equipment_id = :equipment_id";

    // Chuẩn bị câu lệnh
    $stmt = $pdo->prepare($sql);

    // Liên kết các tham số
    $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
    $stmt->bindParam(':equipment_id', $equipmentId, PDO::PARAM_INT);

    // Thực hiện truy vấn và trả về kết quả
    return $stmt->execute();
}

//Lấy dữ liệu từ câu lệnh SQL - Lấy tất cả
function getRaw($sql)
{
    $statement = query($sql, [], true);
    if (is_object($statement)) {
        $dataFetch = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $dataFetch;
    }

    return false;
}

//Lấy dữ liệu từ câu lệnh SQL - Lấy 1 bản ghi
function firstRaw($sql)
{
    $statement = query($sql, [], true);
    if (is_object($statement)) {
        $dataFetch = $statement->fetch(PDO::FETCH_ASSOC);
        return $dataFetch;
    }

    return false;
}

//Lấy dữ liệu theo table, field, condition
function get($table, $field = '*', $condition = '')
{
    $sql = 'SELECT ' . $field . ' FROM ' . $table;
    if (!empty($condition)) {
        $sql .= ' WHERE ' . $condition;
    }

    return getRaw($sql);
}

function first($table, $field = '*', $condition = '')
{
    $sql = 'SELECT ' . $field . ' FROM ' . $table;
    if (!empty($condition)) {
        $sql .= ' WHERE ' . $condition;
    }

    return firstRaw($sql);
}

//function bổ sung
//lấy số dòng câu truy vấn
function getRows($sql)
{
    $statement = query($sql, [], true);
    if (!empty($statement)) {
        return $statement->rowCount();
    }
}

//Lấy id vừa insert
function insertId() {}

function linkTenantToContract($tenantId, $contractId) {
    global $pdo; // Kết nối đến cơ sở dữ liệu

    // Chuẩn bị câu truy vấn
    $sql = "INSERT INTO contract_tenant (tenant_id_1, contract_id_1) VALUES (:tenant_id_1, :contract_id_1)";

    // Thực hiện truy vấn
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tenant_id_1', $tenantId);
        $stmt->bindParam(':contract_id_1', $contractId);
        $stmt->execute();

        return ['success' => true, 'message' => 'Liên kết khách thuê với hợp đồng thành công.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi khi liên kết: ' . $e->getMessage()];
    }
}
