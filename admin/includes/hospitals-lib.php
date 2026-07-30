<?php
declare(strict_types=1);

function hospitalJsonPath(): string {
    return SITE_ROOT . '/assets/data/hospital-locator.json';
}

function hospitalDefaultFacilities(): array {
    return [
        'โรงพยาบาล',
        'คลินิก',
        'ศูนย์ตรวจสุขภาพ',
        'ผู้ป่วยใน',
        'ผู้ป่วยนอก',
        'ทันตกรรม',
        'ห้องฉุกเฉิน',
    ];
}

function hospitalLoadData(): array {
    $path = hospitalJsonPath();
    if (!is_file($path)) {
        return [
            'statusCode' => 200,
            'errorMessage' => null,
            'count' => 0,
            'facetResult' => new stdClass(),
            'results' => [],
        ];
    }
    $raw = file_get_contents($path);
    $data = json_decode($raw ?: '{}', true);
    if (!is_array($data)) {
        return [
            'statusCode' => 200,
            'errorMessage' => null,
            'count' => 0,
            'facetResult' => new stdClass(),
            'results' => [],
        ];
    }
    if (!isset($data['results']) || !is_array($data['results'])) {
        $data['results'] = [];
    }
    return $data;
}

function hospitalSaveData(array $data): void {
    $path = hospitalJsonPath();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (!isset($data['results']) || !is_array($data['results'])) {
        $data['results'] = [];
    }
    $data['count'] = count($data['results']);
    $data['statusCode'] = $data['statusCode'] ?? 200;
    $data['errorMessage'] = $data['errorMessage'] ?? null;

    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($json === false) {
        throw new RuntimeException('ไม่สามารถแปลงข้อมูลเป็น JSON ได้');
    }

    $tmp = $path . '.tmp.' . getmypid();
    if (file_put_contents($tmp, $json . "\n") === false) {
        throw new RuntimeException('เขียนไฟล์ชั่วคราวไม่สำเร็จ');
    }
    if (!rename($tmp, $path)) {
        @unlink($tmp);
        throw new RuntimeException('บันทึกไฟล์โรงพยาบาลไม่สำเร็จ');
    }
}

function hospitalAll(): array {
    return hospitalLoadData()['results'];
}

function hospitalFindById(string $id): ?array {
    foreach (hospitalAll() as $row) {
        if (($row['id'] ?? '') === $id) {
            return $row;
        }
    }
    return null;
}

function hospitalNewId(): string {
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    $hex = bin2hex($bytes);
    return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4)
        . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
}

function hospitalNormalizeFacilities($raw): array {
    if (is_array($raw)) {
        $list = $raw;
    } else {
        $list = preg_split('/[,،\n]+/u', (string) $raw) ?: [];
    }
    $out = [];
    foreach ($list as $item) {
        $item = trim((string) $item);
        if ($item !== '' && !in_array($item, $out, true)) {
            $out[] = $item;
        }
    }
    return $out;
}

function hospitalBuildRecord(array $input, ?array $existing = null): array {
    $name = trim((string) ($input['name'] ?? ''));
    if ($name === '') {
        throw new InvalidArgumentException('กรุณากรอกชื่อสถานพยาบาล');
    }

    $facilities = hospitalNormalizeFacilities($input['facilities'] ?? []);
    $supportIndividual = !empty($input['support_individual']);
    $supportGroup = !empty($input['support_group']);
    if (!$supportIndividual && !$supportGroup) {
        $supportIndividual = true;
        $supportGroup = true;
    }

    $lat = trim((string) ($input['latitude'] ?? ''));
    $lng = trim((string) ($input['longitude'] ?? ''));
    if ($lat === '' || $lng === '') {
        throw new InvalidArgumentException('กรุณากรอกพิกัด Latitude และ Longitude สำหรับแสดงบนแผนที่');
    }
    if (!is_numeric($lat) || !is_numeric($lng)) {
        throw new InvalidArgumentException('พิกัดต้องเป็นตัวเลข');
    }

    $id = trim((string) ($input['id'] ?? ''));
    if ($id === '') {
        $id = $existing['id'] ?? hospitalNewId();
    }

    return [
        'id' => $id,
        'name' => $name,
        'province' => trim((string) ($input['province'] ?? '')),
        'facilities' => $facilities,
        'individualFacilities' => [],
        'groupFacilities' => $supportGroup ? $facilities : [],
        'commonFacilities' => $supportIndividual ? $facilities : [],
        'streetNumber' => trim((string) ($input['streetNumber'] ?? '')),
        'road' => trim((string) ($input['road'] ?? '')),
        'area' => trim((string) ($input['area'] ?? ($input['province'] ?? ''))),
        'district' => trim((string) ($input['district'] ?? '')),
        'county' => trim((string) ($input['county'] ?? '')),
        'postalCode' => trim((string) ($input['postalCode'] ?? '')),
        'longitude' => (string) $lng,
        'latitude' => (string) $lat,
        'telephone' => trim((string) ($input['telephone'] ?? '')),
        'fax' => trim((string) ($input['fax'] ?? '')),
        'mobile' => trim((string) ($input['mobile'] ?? '')),
        'openingHours' => $existing['openingHours'] ?? null,
        '_support_individual' => $supportIndividual,
        '_support_group' => $supportGroup,
    ];
}

function hospitalStripMeta(array $record): array {
    unset($record['_support_individual'], $record['_support_group']);
    return $record;
}

function hospitalUpsert(array $record): void {
    $record = hospitalStripMeta($record);
    $data = hospitalLoadData();
    $found = false;
    foreach ($data['results'] as $i => $row) {
        if (($row['id'] ?? '') === $record['id']) {
            $data['results'][$i] = $record;
            $found = true;
            break;
        }
    }
    if (!$found) {
        array_unshift($data['results'], $record);
    }
    hospitalSaveData($data);
}

function hospitalDelete(string $id): bool {
    $data = hospitalLoadData();
    $before = count($data['results']);
    $data['results'] = array_values(array_filter($data['results'], static function ($row) use ($id) {
        return ($row['id'] ?? '') !== $id;
    }));
    if (count($data['results']) === $before) {
        return false;
    }
    hospitalSaveData($data);
    return true;
}

function hospitalProvinces(array $rows): array {
    $set = [];
    foreach ($rows as $row) {
        $p = trim((string) ($row['province'] ?? ''));
        if ($p !== '') {
            $set[$p] = true;
        }
    }
    $list = array_keys($set);
    sort($list, SORT_STRING);
    return $list;
}

function hospitalFacilityOptions(array $rows): array {
    $set = [];
    foreach (hospitalDefaultFacilities() as $f) {
        $set[$f] = true;
    }
    foreach ($rows as $row) {
        foreach (($row['facilities'] ?? []) as $f) {
            $f = trim((string) $f);
            if ($f !== '') {
                $set[$f] = true;
            }
        }
    }
    $list = array_keys($set);
    sort($list, SORT_STRING);
    return $list;
}

function hospitalFormatAddress(array $h): string {
    return implode(' ', array_filter([
        $h['streetNumber'] ?? '',
        $h['road'] ?? '',
        $h['county'] ?? '',
        $h['district'] ?? '',
        $h['province'] ?? '',
        $h['postalCode'] ?? '',
    ], static fn($v) => trim((string) $v) !== ''));
}

function hospitalSupportsIndividual(array $h): bool {
    return !empty($h['individualFacilities']) || !empty($h['commonFacilities']);
}

function hospitalSupportsGroup(array $h): bool {
    return !empty($h['groupFacilities']);
}

function hospitalFilterRows(array $rows, array $filters): array {
    $q = mb_strtolower(trim((string) ($filters['q'] ?? '')), 'UTF-8');
    $province = trim((string) ($filters['province'] ?? ''));

    return array_values(array_filter($rows, static function ($row) use ($q, $province) {
        if ($province !== '' && ($row['province'] ?? '') !== $province) {
            return false;
        }
        if ($q === '') {
            return true;
        }
        $hay = mb_strtolower(implode(' ', [
            $row['name'] ?? '',
            $row['province'] ?? '',
            $row['district'] ?? '',
            $row['county'] ?? '',
            $row['road'] ?? '',
            $row['streetNumber'] ?? '',
            implode(' ', $row['facilities'] ?? []),
        ]), 'UTF-8');
        return mb_strpos($hay, $q, 0, 'UTF-8') !== false;
    }));
}
