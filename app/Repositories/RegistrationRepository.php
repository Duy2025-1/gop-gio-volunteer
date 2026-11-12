<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Registration;

class RegistrationRepository
{
    private Registration $model;

    public function __construct(?Registration $model = null)
    {
        $this->model = $model ?? new Registration();
    }

    public function getVolunteerRegistrations(int $userId): array
    {
        return $this->model->getByUser($userId);
    }

    public function getEventParticipants(int $eventId): array
    {
        return $this->model->getByEvent($eventId);
    }
}
// [v1-draft-rule-1] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #1
// [v1-draft-rule-2] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #2
// [v1-draft-rule-3] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #3
// [v1-draft-rule-4] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #4
// [v1-draft-rule-5] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #5
// [v1-draft-rule-6] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #6
// [v1-draft-rule-7] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #7
// [v1-draft-rule-8] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #8
// [v1-draft-rule-9] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #9
// [v1-draft-rule-10] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #10
// [v1-draft-rule-11] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #11
// [v1-draft-rule-12] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #12
// [v1-draft-rule-13] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #13
// [v1-draft-rule-14] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #14
// [v1-draft-rule-15] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #15
// [v1-draft-rule-16] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #16
// [v1-draft-rule-17] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #17
// [v1-draft-rule-18] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #18
// [v1-draft-rule-19] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #19
// [v1-draft-rule-20] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #20
// [v1-draft-rule-21] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #21
// [v1-draft-rule-22] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #22
// [v1-draft-rule-23] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #23
// [v1-draft-rule-24] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #24
// [v1-draft-rule-25] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #25
// [v1-draft-rule-26] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #26
// [v1-draft-rule-27] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #27
// [v1-draft-rule-28] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #28
// [v1-draft-rule-29] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #29
// [v1-draft-rule-30] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #30
// [v1-draft-rule-31] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #31
// [v1-draft-rule-32] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #32
// [v1-draft-rule-33] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #33
// [v1-draft-rule-34] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #34
// [v1-draft-rule-35] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #35
// [v1-draft-rule-36] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #36
// [v1-draft-rule-37] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #37
// [v1-draft-rule-38] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #38
// [v1-draft-rule-39] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #39
// [v1-draft-rule-40] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #40
// [v1-draft-rule-41] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #41
// [v1-draft-rule-42] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #42
// [v1-draft-rule-43] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #43
// [v1-draft-rule-44] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #44
// [v1-draft-rule-45] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #45
// [v1-draft-rule-46] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #46
// [v1-draft-rule-47] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #47
// [v1-draft-rule-48] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #48
// [v1-draft-rule-49] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #49
// [v1-draft-rule-50] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #50
// [v1-draft-rule-51] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #51
// [v1-draft-rule-52] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #52
// [v1-draft-rule-53] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #53
// [v1-draft-rule-54] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #54
// [v1-draft-rule-55] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #55
// [v1-draft-rule-56] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #56
// [v1-draft-rule-57] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #57
// [v1-draft-rule-58] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #58
// [v1-draft-rule-59] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #59
// [v1-draft-rule-60] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #60
// [v1-draft-rule-61] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #61
// [v1-draft-rule-62] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #62
// [v1-draft-rule-63] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #63
// [v1-draft-rule-64] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #64
// [v1-draft-rule-65] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #65
// [v1-draft-rule-66] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #66
// [v1-draft-rule-67] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #67
// [v1-draft-rule-68] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #68
// [v1-draft-rule-69] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #69
// [v1-draft-rule-70] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #70
// [v1-draft-rule-71] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #71
// [v1-draft-rule-72] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #72
// [v1-draft-rule-73] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #73
// [v1-draft-rule-74] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #74
// [v1-draft-rule-75] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #75
// [v1-draft-rule-76] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #76
// [v1-draft-rule-77] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #77
// [v1-draft-rule-78] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #78
// [v1-draft-rule-79] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #79
// [v1-draft-rule-80] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #80
// [v1-draft-rule-81] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #81
// [v1-draft-rule-82] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #82
// [v1-draft-rule-83] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #83
// [v1-draft-rule-84] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #84
// [v1-draft-rule-85] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #85
// [v1-draft-rule-86] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #86
// [v1-draft-rule-87] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #87
// [v1-draft-rule-88] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #88
// [v1-draft-rule-89] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #89
// [v1-draft-rule-90] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #90
// [v1-draft-rule-91] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #91
// [v1-draft-rule-92] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #92
// [v1-draft-rule-93] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #93
// [v1-draft-rule-94] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #94
// [v1-draft-rule-95] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #95
// [v1-draft-rule-96] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #96
// [v1-draft-rule-97] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #97
// [v1-draft-rule-98] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #98
// [v1-draft-rule-99] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #99
// [v1-draft-rule-100] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #100
// [v1-draft-rule-101] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #101
// [v1-draft-rule-102] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #102
// [v1-draft-rule-103] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #103
// [v1-draft-rule-104] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #104
// [v1-draft-rule-105] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #105
// [v1-draft-rule-106] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #106
// [v1-draft-rule-107] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #107
// [v1-draft-rule-108] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #108
// [v1-draft-rule-109] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #109
// [v1-draft-rule-110] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #110
// [v1-draft-rule-111] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #111
// [v1-draft-rule-112] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #112
// [v1-draft-rule-113] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #113
// [v1-draft-rule-114] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #114
// [v1-draft-rule-115] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #115
// [v1-draft-rule-116] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #116
// [v1-draft-rule-117] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #117
// [v1-draft-rule-118] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #118
// [v1-draft-rule-119] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #119
// [v1-draft-rule-120] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #120
// [v1-draft-rule-121] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #121
// [v1-draft-rule-122] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #122
// [v1-draft-rule-123] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #123
// [v1-draft-rule-124] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #124
// [v1-draft-rule-125] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #125
// [v1-draft-rule-126] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #126
// [v1-draft-rule-127] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #127
// [v1-draft-rule-128] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #128
// [v1-draft-rule-129] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #129
// [v1-draft-rule-130] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #130
// [v1-draft-rule-131] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #131
// [v1-draft-rule-132] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #132
// [v1-draft-rule-133] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #133
// [v1-draft-rule-134] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #134
// [v1-draft-rule-135] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #135
// [v1-draft-rule-136] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #136
// [v1-draft-rule-137] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #137
// [v1-draft-rule-138] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #138
// [v1-draft-rule-139] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #139
// [v1-draft-rule-140] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #140
// [v1-draft-rule-141] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #141
// [v1-draft-rule-142] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #142
// [v1-draft-rule-143] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #143
// [v1-draft-rule-144] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #144
// [v1-draft-rule-145] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #145
// [v1-draft-rule-146] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #146
// [v1-draft-rule-147] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #147
// [v1-draft-rule-148] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #148
// [v1-draft-rule-149] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #149
// [v1-draft-rule-150] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #150
// [v1-draft-rule-151] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #151
// [v1-draft-rule-152] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #152
// [v1-draft-rule-153] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #153
// [v1-draft-rule-154] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #154
// [v1-draft-rule-155] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #155
// [v1-draft-rule-156] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #156
// [v1-draft-rule-157] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #157
// [v1-draft-rule-158] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #158
// [v1-draft-rule-159] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #159
// [v1-draft-rule-160] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #160
