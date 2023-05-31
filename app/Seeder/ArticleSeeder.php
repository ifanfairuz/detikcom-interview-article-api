<?php

namespace App\Seeder;

use App\Repository\Article;

class ArticleSeeder
{
    public function datas()
    {
        return [
            [
                'title' => "Cathie Wood mengatakan stok perangkat lunak adalah taruhan AI berikutnya setelah Nvidia",
                "summary" => "\"Kami mencari penyedia perangkat lunak yang benar-benar berada di posisi Nvidia saat pertama kali kami membelinya,\" Wood, CEO dan pendiri Ark Investment Management LLC, mengatakan kepada Bloomberg TV pada hari Rabu.",
                "author" => "detikedu",
                "position" => 2,
            ],
            [
                'title' => "Elon Musk memulai hari kedua kunjungan China setelah menekankan ikatan",
                "summary" => "Kunjungan Elon Musk ke China: Tesla ingin mengejar kesepakatan yang serupa dengan yang diumumkan Ford Motor Co. pada bulan Februari dengan CATL untuk membangun pabrik yang sepenuhnya dimiliki oleh pembuat mobil AS",
                "author" => "detikedu",
                "position" => 3,
            ],
            [
                'title' => "Kru astronot pribadi, termasuk wanita Arab pertama di orbit, kembali dari stasiun luar angkasa",
                "summary" => "(marketscreener.com) -Sebuah tim astronot swasta yang terdiri dari dua orang Amerika dan dua orang Saudi, termasuk wanita Arab pertama yang dikirim ke orbit, mendarat dengan aman di lepas pantai Florida pada Selasa malam, mengakhiri misi penelitian delapan hari di luar angkasa Internasional Stasiun",
                "author" => "detikedu",
                "position" => 5,
            ]
        ];
    }

    public function run()
    {
        $repo = new Article();
        foreach ($this->datas() as $data) {
            $repo->insert($data);
        }
    }
}
