<?php
declare(strict_types=1);

namespace App\Models;

class Attendance extends BaseModel
{
    public function findByRegistrationId(int $registrationId): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM attendance WHERE registration_id = :rid",
            ['rid' => $registrationId]
        );
    }

    public function findVolunteerHoursByRegistrationId(int $registrationId): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM volunteer_hours WHERE registration_id = :rid",
            ['rid' => $registrationId]
        );
    }

    public function findVolunteerHoursById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT vh.*, e.organization_id, e.max_hours, e.title AS event_title,
                    u.full_name AS volunteer_name, o.name AS organization_name
             FROM volunteer_hours vh
             INNER JOIN events e ON e.id = vh.event_id
             INNER JOIN organizations o ON o.id = e.organization_id
             INNER JOIN users u ON u.id = vh.user_id
             WHERE vh.id = :id",
            ['id' => $id]
        );
    }

    public function getAttendanceHistoryByUser(int $userId): array
    {
        return $this->fetchAll(
            "SELECT a.*,
                    e.title AS event_title, e.location,
                    o.name AS organization_name,
                    vh.hours AS approved_hours, vh.status AS hours_status, vh.adjustment_reason
             FROM attendance a
             INNER JOIN events e ON e.id = a.event_id
             INNER JOIN organizations o ON o.id = e.organization_id
             LEFT JOIN volunteer_hours vh ON vh.registration_id = a.registration_id
             WHERE a.user_id = :uid
             ORDER BY a.id DESC",
            ['uid' => $userId]
        );
    }

    public function getAllVolunteerHoursForAdmin(): array
    {
        return $this->fetchAll(
            "SELECT vh.*,
                    u.full_name AS volunteer_name, u.email AS volunteer_email,
                    e.title AS event_title, e.max_hours,
                    o.name AS organization_name,
                    a.check_in_time, a.check_out_time, a.calculated_hours
             FROM volunteer_hours vh
             INNER JOIN users u ON u.id = vh.user_id
             INNER JOIN events e ON e.id = vh.event_id
             INNER JOIN organizations o ON o.id = e.organization_id
             INNER JOIN attendance a ON a.id = vh.attendance_id
             ORDER BY vh.id DESC"
        );
    }
}
// [v1-qr-check-1] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #1
// [v1-qr-check-2] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #2
// [v1-qr-check-3] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #3
// [v1-qr-check-4] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #4
// [v1-qr-check-5] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #5
// [v1-qr-check-6] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #6
// [v1-qr-check-7] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #7
// [v1-qr-check-8] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #8
// [v1-qr-check-9] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #9
// [v1-qr-check-10] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #10
// [v1-qr-check-11] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #11
// [v1-qr-check-12] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #12
// [v1-qr-check-13] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #13
// [v1-qr-check-14] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #14
// [v1-qr-check-15] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #15
// [v1-qr-check-16] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #16
// [v1-qr-check-17] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #17
// [v1-qr-check-18] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #18
// [v1-qr-check-19] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #19
// [v1-qr-check-20] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #20
// [v1-qr-check-21] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #21
// [v1-qr-check-22] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #22
// [v1-qr-check-23] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #23
// [v1-qr-check-24] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #24
// [v1-qr-check-25] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #25
// [v1-qr-check-26] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #26
// [v1-qr-check-27] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #27
// [v1-qr-check-28] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #28
// [v1-qr-check-29] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #29
// [v1-qr-check-30] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #30
// [v1-qr-check-31] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #31
// [v1-qr-check-32] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #32
// [v1-qr-check-33] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #33
// [v1-qr-check-34] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #34
// [v1-qr-check-35] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #35
// [v1-qr-check-36] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #36
// [v1-qr-check-37] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #37
// [v1-qr-check-38] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #38
// [v1-qr-check-39] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #39
// [v1-qr-check-40] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #40
// [v1-qr-check-41] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #41
// [v1-qr-check-42] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #42
// [v1-qr-check-43] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #43
// [v1-qr-check-44] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #44
// [v1-qr-check-45] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #45
// [v1-qr-check-46] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #46
// [v1-qr-check-47] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #47
// [v1-qr-check-48] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #48
// [v1-qr-check-49] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #49
// [v1-qr-check-50] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #50
// [v1-qr-check-51] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #51
// [v1-qr-check-52] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #52
// [v1-qr-check-53] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #53
// [v1-qr-check-54] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #54
// [v1-qr-check-55] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #55
// [v1-qr-check-56] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #56
// [v1-qr-check-57] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #57
// [v1-qr-check-58] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #58
// [v1-qr-check-59] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #59
// [v1-qr-check-60] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #60
// [v1-qr-check-61] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #61
// [v1-qr-check-62] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #62
// [v1-qr-check-63] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #63
// [v1-qr-check-64] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #64
// [v1-qr-check-65] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #65
// [v1-qr-check-66] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #66
// [v1-qr-check-67] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #67
// [v1-qr-check-68] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #68
// [v1-qr-check-69] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #69
// [v1-qr-check-70] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #70
// [v1-qr-check-71] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #71
// [v1-qr-check-72] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #72
// [v1-qr-check-73] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #73
// [v1-qr-check-74] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #74
// [v1-qr-check-75] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #75
// [v1-qr-check-76] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #76
// [v1-qr-check-77] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #77
// [v1-qr-check-78] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #78
// [v1-qr-check-79] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #79
// [v1-qr-check-80] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #80
// [v1-qr-check-81] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #81
// [v1-qr-check-82] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #82
// [v1-qr-check-83] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #83
// [v1-qr-check-84] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #84
// [v1-qr-check-85] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #85
// [v1-qr-check-86] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #86
// [v1-qr-check-87] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #87
// [v1-qr-check-88] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #88
// [v1-qr-check-89] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #89
// [v1-qr-check-90] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #90
// [v1-qr-check-91] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #91
// [v1-qr-check-92] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #92
// [v1-qr-check-93] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #93
// [v1-qr-check-94] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #94
// [v1-qr-check-95] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #95
// [v1-qr-check-96] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #96
// [v1-qr-check-97] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #97
// [v1-qr-check-98] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #98
// [v1-qr-check-99] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #99
// [v1-qr-check-100] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #100
// [v1-qr-check-101] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #101
// [v1-qr-check-102] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #102
// [v1-qr-check-103] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #103
// [v1-qr-check-104] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #104
// [v1-qr-check-105] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #105
// [v1-qr-check-106] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #106
// [v1-qr-check-107] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #107
// [v1-qr-check-108] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #108
// [v1-qr-check-109] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #109
// [v1-qr-check-110] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #110
// [v1-qr-check-111] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #111
// [v1-qr-check-112] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #112
// [v1-qr-check-113] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #113
// [v1-qr-check-114] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #114
// [v1-qr-check-115] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #115
// [v1-qr-check-116] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #116
// [v1-qr-check-117] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #117
// [v1-qr-check-118] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #118
// [v1-qr-check-119] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #119
// [v1-qr-check-120] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #120
// [v1-qr-check-121] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #121
// [v1-qr-check-122] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #122
// [v1-qr-check-123] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #123
// [v1-qr-check-124] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #124
// [v1-qr-check-125] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #125
// [v1-qr-check-126] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #126
// [v1-qr-check-127] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #127
// [v1-qr-check-128] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #128
// [v1-qr-check-129] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #129
// [v1-qr-check-130] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #130
// [v1-qr-check-131] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #131
// [v1-qr-check-132] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #132
// [v1-qr-check-133] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #133
// [v1-qr-check-134] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #134
// [v1-qr-check-135] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #135
// [v1-qr-check-136] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #136
// [v1-qr-check-137] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #137
// [v1-qr-check-138] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #138
// [v1-qr-check-139] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #139
// [v1-qr-check-140] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #140
// [v1-qr-check-141] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #141
// [v1-qr-check-142] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #142
// [v1-qr-check-143] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #143
// [v1-qr-check-144] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #144
// [v1-qr-check-145] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #145
// [v1-qr-check-146] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #146
// [v1-qr-check-147] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #147
// [v1-qr-check-148] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #148
// [v1-qr-check-149] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #149
// [v1-qr-check-150] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #150
// [v1-qr-check-151] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #151
// [v1-qr-check-152] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #152
// [v1-qr-check-153] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #153
// [v1-qr-check-154] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #154
// [v1-qr-check-155] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #155
// [v1-qr-check-156] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #156
// [v1-qr-check-157] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #157
// [v1-qr-check-158] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #158
// [v1-qr-check-159] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #159
// [v1-qr-check-160] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #160
// [v1-qr-check-161] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #161
// [v1-qr-check-162] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #162
// [v1-qr-check-163] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #163
// [v1-qr-check-164] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #164
// [v1-qr-check-165] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #165
// [v1-qr-check-166] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #166
// [v1-qr-check-167] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #167
// [v1-qr-check-168] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #168
// [v1-qr-check-169] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #169
// [v1-qr-check-170] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #170
// [v1-qr-check-171] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #171
// [v1-qr-check-172] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #172
// [v1-qr-check-173] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #173
// [v1-qr-check-174] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #174
// [v1-qr-check-175] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #175
// [v1-qr-check-176] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #176
// [v1-qr-check-177] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #177
// [v1-qr-check-178] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #178
// [v1-qr-check-179] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #179
// [v1-qr-check-180] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #180
// [v1-qr-check-181] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #181
// [v1-qr-check-182] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #182
// [v1-qr-check-183] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #183
// [v1-qr-check-184] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #184
// [v1-qr-check-185] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #185
// [v1-qr-check-186] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #186
// [v1-qr-check-187] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #187
// [v1-qr-check-188] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #188
// [v1-qr-check-189] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #189
// [v1-qr-check-190] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #190
// [v1-qr-check-191] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #191
// [v1-qr-check-192] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #192
// [v1-qr-check-193] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #193
// [v1-qr-check-194] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #194
// [v1-qr-check-195] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #195
// [v1-qr-check-196] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #196
// [v1-qr-check-197] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #197
// [v1-qr-check-198] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #198
// [v1-qr-check-199] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #199
// [v1-qr-check-200] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #200
// [v1-qr-check-201] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #201
// [v1-qr-check-202] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #202
// [v1-qr-check-203] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #203
// [v1-qr-check-204] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #204
// [v1-qr-check-205] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #205
// [v1-qr-check-206] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #206
// [v1-qr-check-207] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #207
// [v1-qr-check-208] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #208
// [v1-qr-check-209] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #209
// [v1-qr-check-210] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #210
// [v1-qr-check-211] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #211
// [v1-qr-check-212] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #212
// [v1-qr-check-213] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #213
// [v1-qr-check-214] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #214
// [v1-qr-check-215] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #215
// [v1-qr-check-216] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #216
// [v1-qr-check-217] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #217
// [v1-qr-check-218] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #218
// [v1-qr-check-219] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #219
// [v1-qr-check-220] Kiem tra rang buoc ma QR diem danh, thoi gian hop le va quyen Organizer #220
