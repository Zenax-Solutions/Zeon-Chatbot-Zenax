<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BusinessData;

class BusinessDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Services (English + Sinhala)
        BusinessData::create(['content' => 'We offer web hosting solutions for businesses.']);
        BusinessData::create(['content' => 'අපි ව්‍යාපාර සඳහා වෙබ් හෝස්ටින් විසඳුම් ලබා දෙනවා.']);

        BusinessData::create(['content' => 'We design and develop custom websites.']);
        BusinessData::create(['content' => 'අපි අභිරුචි වෙබ් අඩවි නිර්මාණය කරමු.']);

        BusinessData::create(['content' => 'We build e-commerce websites for online businesses.']);
        BusinessData::create(['content' => 'අපි මාරුකරණ වෙබ් අඩවි සාදන්නෙමු.']);

        BusinessData::create(['content' => 'We develop POS and ERP systems for retail and small businesses.']);
        BusinessData::create(['content' => 'අපි සුළු ව්‍යාපාර සඳහා POS සහ ERP පද්ධති සංවර්ධනය කරමු.']);

        BusinessData::create(['content' => 'We provide domain registration and DNS management services.']);
        BusinessData::create(['content' => 'අපි domain ලියාපදිංචි කිරීම සහ DNS කළමනාකරණය ලබා දෙනවා.']);

        // Packages with LKR prices
        BusinessData::create(['content' => 'We offer website packages starting from LKR 18,000.']);
        BusinessData::create(['content' => 'අපගේ වෙබ් අඩවි පැකේජ LKR 18,000 සිට ආරම්භ වේ.']);

        BusinessData::create(['content' => 'Business website packages start at LKR 25,000 with free hosting.']);
        BusinessData::create(['content' => 'ව්‍යාපාරික වෙබ් අඩවි පැකේජ LKR 25,000 සිට, නොමිලේ හෝස්ටින් සමඟ.']);

        BusinessData::create(['content' => 'E-commerce website packages start at LKR 45,000 including shopping cart and payment gateway.']);
        BusinessData::create(['content' => 'ඉ-වාණිජ්‍ය වෙබ් අඩවි පැකේජ LKR 45,000 සිට, shopping cart සහ ගෙවීම් gateway සමඟ.']);

        // Contact & Business Info
        BusinessData::create(['content' => 'Our hotline and WhatsApp: +94713074118.']);
        BusinessData::create(['content' => 'අපගේ හොට්ලයින් සහ WhatsApp: +94713074118.']);

        BusinessData::create(['content' => 'Address: Oruthota, 172/13 Wilthera Uyana Rd, Gampaha 11870.']);
        BusinessData::create(['content' => 'ලිපිනය: ඔරුතොට, 172/13 විල්තෙර උයන පාර, ගම්පහ 11870.']);

        BusinessData::create(['content' => 'Business Hours: Monday to Friday, 9 AM to 5:30 PM. Closed on weekends and Poya days.']);
        BusinessData::create(['content' => 'ව්‍යාපාරික වේලාවන්: සඳුදා සිට සිකුරාදා දක්වා, උදෑසන 9 සිට 5.30 දක්වා. සෙනසුරාදා, ඉරිදා සහ පොය දිනයන්හි වසා ඇත.']);

        BusinessData::create(['content' => 'Google Profile: https://g.co/kgs/Y1H4g9V']);
        BusinessData::create(['content' => 'අපගේ ගූගල් පැතිකඩ: https://g.co/kgs/Y1H4g9V']);

        // Testimonials
        BusinessData::create(['content' => '“Professional and reliable service. One of the best website creators I have worked with.” – Irosha Alahakoon']);
        BusinessData::create(['content' => '“වෘත්තීයමය සහ විශ්වාසදායක සේවාවක්. මම වැඩ කළ හොඳම වෙබ් නිර්මාණකරුවන්ගෙන් එකකි.” – ඉරෝෂා අලහකෝන්']);

        BusinessData::create(['content' => '“Friendly team, highly skilled, and excellent customer service.” – Mangala Suraweera']);
        BusinessData::create(['content' => '“හිතකරුකමකින් යුත් කණ්ඩායමක්, ඉහළ දක්ෂතාවයකින් යුතුව, විශිෂ්ට සේවාලාභී සේවාවක්.” – මංගල සුරවීර']);

        BusinessData::create(['content' => '“Modern design and impressive functionality. Highly recommend Zenax for web design.” – Thinara Suraweera']);
        BusinessData::create(['content' => '“නවීන නිර්මාණයක් සහ අත්භූත ක්‍රියාකාරිත්වයක්. Zenax වෙබ් නිර්මාණය සඳහා ඉතා නිර්දේශ කරමි.” – තිනාරා සුරවීර']);

        // Mission/Tagline
        BusinessData::create(['content' => 'ZENAX – Empowering your business through digital solutions, from websites to video editing.']);
        BusinessData::create(['content' => 'ZENAX – ඔබේ ව්‍යාපාරය ඩිජිටල් විසඳුම් හරහා බලවත් කරමින් – වෙබ් අඩවි වලින් විඩියෝ සංස්කරණය දක්වා.']);
        BusinessData::create(['content' => '“Your digital partner for web solutions.”']);
        BusinessData::create(['content' => '“ඔබේ ඩිජිටල් සහකාරයා වෙබ් විසඳුම් සඳහා.”']);
        BusinessData::create(['content' => '“Your one-stop solution for all your digital needs.”']);
    }
}
