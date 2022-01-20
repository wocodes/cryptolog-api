<?php

use App\Models\User;
use Binance\API;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


//const STATUS_CODES = [
//    "OK" => 200,
//    "Created" => 201,
//    "Unauthorized" => 401,
//    "Bad Request" => 400,
//    "Forbidden" => 403,
//    "Not Found" => 404,
//    "Internal Server Error" => 500,
//];


require_once 'apis/admin.php';
require_once 'apis/users.php';

require 'apis/assets.php';

require 'apis/logs.php';
require_once 'apis/platforms.php';
require_once 'apis/fiat.php';

Route::post("waitlist", "\App\Actions\Waitlist\Add");

Route::get('test', function() {

});

Route::get('send-sms', function() {
    $sendToOnlyAdmin = false;
    $sendToLSAMembersOnly = false;
    $msgIsFeastReminder = true;

    $admin = [
        "William" => "09024226411",
        "William2" => "07068535208",
    ];

    $LsaMembers = [
        // LSA Members
        // "William" => "09024226411",
        "William2" => "07068535208",
        "Emma" => "07060961293",
        "Affiong" => "08034269494",
        "Tahirih" => "08100944660",
        "Ebere" => "08167189361",
        "Nabil" => "08025481292",
        "Robert" => "08027486661",
        "Mrs Tom-Jones" => "08034269494",
        "Bolaji" => "09072762497",
        'Robert Onyemaechi. Uzu' => '08035757549'
    ];


// // // Community Members
    $otherMembers = [
        "Francis" => "08051829321",
        "Lizzy Ngishe" => "07033746793",
        "Glory Okoro" => "08130645132",
        "Leah Hills" => "09093878755",
        "Luke Hills" => "08136888608",
        "Agnes Njang" => "07033672214",
        "Tosin Tom-Jones" => "07061948616",
        "Mrs. Patricia Okoro" => "08161192836",
        "Shoghi Hills" => "08122056152",
        "Mrs. Folashade" => "08169102984",
        "Wasiu Adisa"	=> "8060531115",
        'Biodun Afolabi' =>	"8028662737",
        'Dehinde Afolabi' =>	'8022102433',
        'Funmilayo Afolabi'	=> '8032425060',
        'Sunkanmi Afolabi' =>	'8027086698',
        'Titilayo Afolabi'	=> "8068566800",
        'Ololade Ajoke Akindele' =>	'7040968440',
        'Adreas Ako' =>	'7089840627',
        'Molayo Aladeetan' =>	'8063227089',
        'Bates Arsan'	=> '8071492684',
        'Florence Bassey'	=> '7042398094',
        'Adewale Bello'	=> '8053633567',
        'John Dominic' =>	'8056113764',
        'Patience Ebi'	=> '8055441228',
        'OC  Elaigwu'	=> '8025907709',
        'Florence Johnson' =>	'8103307920',
        'Ini Johnson' 	=> '8063236008',
        'Alfred Josiah'	=> '7062293700',
        'Sade Josiah'	=> '8029305714',
        'Nicholas Jumbo'	=> '8035123324',
        'Chioma Nguma'	=> '8063261489',
        'Ugo Irem   Nnachi' =>	'8034738353',
        'Dorathy Ntekume'	=> '7027691761',
        'Ogbonna  Nwachukwu'	=> '8035723603',
        'Okechukwu Nwadike'	=> '8130059741',
        'Joy Nweke'	=> '8030694797',
        'Opeyemi Obamila' =>	'7060911434',
        'Stephen Obamila'	=> '8056884666',
        'Humphrey Ogochukwu  Okaludo' =>	'8096812105',
        'Sodiq Okorede'	=> '8161149989',
        'Simon Eleje  Okoro'	=> '7066566668',
        'Odunayo.O. Olotu'	=> '8027813888',
        'Olayisoye Olojede'	=> '8060848881',
        'Hannah Oparinde'	=> '7042959857',
        'Chukwuma Oshiokpu' =>	'8028041364',
        'Samuel Osuji'	=> '8066938956',
        'Abrahim Salami' 	=> '8129326611',
        'Kolawole Hassan   Sanjo' =>	'8051017227',
        'Ade Tomjones'	=> '8027400350',
        'Anis Onyekachukwu  Uzu'	=> '8035757549',
    ];

    $allNumbers = $sendToOnlyAdmin ?
        $admin :
        array_merge(
            $LsaMembers,
            $sendToLSAMembersOnly ? [] : $otherMembers
        );

    $initialMessages = [
        "It hath been enjoined upon you once a month to offer hospitality, even should ye serve no more than water, for God hath willed to bind your hearts together, though it be through heavenly and earthly means combined...",

        "Do not be content with showing friendship in words alone, let your heart burn with loving kindness for all who may cross your path. - Baha'u'llah",

        "Be generous in prosperity, and thankful in adversity... - Baha'u'llah",

        "Be worthy of the trust of thy neighbor, and look upon him with a bright and friendly face... - Baha'u'llah",

        "Be a treasure to the poor, an admonisher to the rich, an answerer of the cry of the needy, a preserver of the sanctity of thy pledge... - Baha'u'llah",

        "Be fair in thy judgment, and guarded in thy speech... - Baha'u'llah",

        "Be unjust to no man, and show all meekness to all men... - Baha'u'llah",

        "Be as a lamp unto them that walk in darkness, a joy to the sorrowful, a sea for the thirsty, a haven for the distressed, an upholder and defender of the victim of oppression... - Baha'u'llah",

        "Let integrity and uprightness distinguish all thine acts. - Baha'u'llah",

        "Be a home for the stranger, a balm to the suffering, a tower of strength for the fugitive. - Baha'u'llah",

        "Be eyes to the blind, and a guiding light unto the feet of the erring. - Baha'u'llah",

        "Be an ornament to the countenance of truth, a crown to the brow of fidelity, a pillar of the temple of righteousness, a breath of life to the body of mankind, an ensign of the hosts of justice, a luminary above the horizon of virtue... - Baha'u'llah",

        "Be a dew to the soil of the human heart, an ark on the ocean of knowledge, a sun in the heaven of bounty, a gem on the diadem of wisdom, a shining light in the firmament of thy generation, a fruit upon the tree of humility... - Baha'u'llah",

        "The earth is but one country and mankind its citizens... - Baha'u'llah",

        "That one indeed is a man who, today, dedicateth himself to the service of the entire human race. - Baha'u'llah",

        "The Great Being saith: Blessed and happy is he that ariseth to promote the best interests of the peoples and kindreds of the earth. - Baha'u'llah",

        "It is not for him to pride himself who loveth his own country, but rather for him who loveth the whole world. - Baha'u'llah",

        "Regard man as a mine rich in gems of inestimable value. Education can, alone, cause it to reveal its treasures, and enable mankind to benefit there from... - Baha'u'llah",

        "A thankful person is thankful under all circumstances. A complaining soul complains even in paradise... - Baha'u'llah",

        "Religion without science is superstition. Science without religion is materialism... - Baha'u'llah",

        "Religious fanaticism and hatred are a world-devouring fire, whose violence none can quench... - Baha'u'llah",

        "Say: o brethren! Let deeds, not words, be your adorning. - Baha'u'llah",

        "The betterment of the world can be accomplished through pure and goodly deeds and through commendable and seemly conduct. - Baha'u'llah",

        "Let your vision be world embracing rather than confined to your own self. - Baha'u'llah",

        "All peoples and nations are of one family, the children of one Father, and should be to one another as brothers and sisters. - Baha'u'llah",

        "Beautify your tongues, O people, with truthfulness, and adorn your souls with the ornament of honesty. Beware, O people, that ye deal not treacherously with any one ― Baha'u'llah",

        "The utterance of God is a lamp, whose light is these words: Ye are the fruits of one tree, and the leaves of one branch. Deal ye one with another with the utmost love and harmony, with friendliness and fellowship. ... So powerful is the light of unity that it can illuminate the whole earth. - Baha'u'llah",

        "A kindly tongue is the lodestone of the hearts of men. It is the bread of the spirit, it clotheth the words with meaning, it is the fountain of the light of wisdom and understanding. - Baha'u'llah",

        "Dedicate the precious days of your lives to the betterment of the world. - Baha'u'llah",

        "Man's merit lieth in service and virtue and not in the pageantry of wealth and riches. - Baha'u'llah",

        "Noble have I created thee, yet thou hast abased thyself. Rise then unto that for which thou wast created. - Baha'u'llah",

        "The essence of faith is fewness of words and abundance of deeds. - Baha'u'llah",

        "O ye that dwell on earth! The religion of God is for love and unity; make it not the cause of enmity or dissension. - Baha'u'llah",

        "O Son of Spirit! My first counsel is this: Possess a pure, kindly and radiant heart, that thine may be a sovereignty ancient, imperishable and everlasting. - Baha'u'llah",

        "Dost thou reckon thyself only a puny form/When within thee the universe is folded? ― Baha'u'llah",

        "My Calamity is my providence, outwardly it is fire and vengeance, but inwardly it is light and mercy. - Baha'u'llah",

        "Out of the wastes of nothingness, with the clay of My command I made thee to appear, and have ordained for thy training every atom of existence and the essence of all created things. - Baha'u'llah",

        "Holy words and pure and goodly deeds ascend unto the heaven of celestial glory. - Baha'u'llah",

        "Humility exalteth man to the heaven of glory and power, whilst pride abaseth him to the depths of wretchedness and degradation. - Baha'u'llah",

        "Be patient under all conditions and place your whole trust and confidence in God. - Baha'u'llah",

        "Blessed is he who preferreth his brother before himself. - Baha'u'llah",

        "The essence of true safety is to observe silence, to look at the end of things and to renounce the world. - Baha'u'llah",

        "The source of all glory is acceptance of whatsoever the Lord hath bestowed, and contentment with that which God hath ordained. - Baha'u'llah",

        "All that ye potentially possess can…be manifested only as a result of your own volition. - Baha'u'llah",

        "O CHILDREN OF ADAM! Holy words and pure and goodly deeds ascend unto the heaven of celestial glory. Strive that your deeds may be cleansed from the dust of self and hypocrisy and find favor at the court of glory... - Baha'u'llah",

        "O SON OF LIGHT! Forget all save Me and commune with My spirit. This is of the essence of My command, therefore turn unto it. - Baha'u'llah",

        "Busy not thyself with this world, for with fire We test the gold, and with gold We test Our servants..",

        "Walk ye in the ways of the good pleasure of the Friend, and know that His pleasure is in the pleasure of His creatures. That is: no man should enter the house of his friend save at his friend’s pleasure, nor lay hands upon his treasures nor prefer his own will to his friend’s, and in no wise seek an advantage over him. - Baha'u'llah",

        "For everything there is a sign. The sign of love is fortitude under My decree and patience under My trials. - Baha'u'llah",

        "Where there is love, nothing is too much trouble and there is always time. - Abdu'l-Baha",

        "Love the creatures for the sake of God and not for themselves. You will never become angry or impatient if you love them for the sake of God... - Abdu'l-Baha",

        "Humanity is not perfect. There are imperfections in every human being, and you will always become unhappy if you look toward the people themselves. But if you look toward God, you will love them and be kind to them, for the world of God is the world of perfection and complete mercy... - Abdu'l-Baha",

        "Therefore, do not look at the shortcomings of anybody; see with the sight of forgiveness... - Abdu'l-Baha",

        "The imperfect eye beholds imperfections. The eye that covers faults looks toward the Creator of souls... - Abdu'l-Baha",

        "...You must love and be kind to everybody, care for the poor, protect the weak, heal the sick, teach and educate the ignorant. - Abdu'l-Baha",

        "My heart is in a constant state of thanksgiving. - Abdu'l-Baha",

        "That one indeed is a man who, today, dedicateth himself to the service of the entire human race. - Abdu'l-Baha",

        "The intellect is good but until it has become the servant of the heart, it is of little avail... - Abdu'l-Baha",

        "One must see in every human being only that which is worthy of praise. When this is done, one can be a friend to the whole human race. If, however, we look at people from the standpoint of their faults, then being a friend to them is a formidable task... - Abdu'l-Baha",

        "...Thus is it incumbent upon us, when we direct our gaze toward other people, to see where they excel, not where they fail. - Abdu'l-Baha",

        "Be not the slave of your moods, but their master. But if you are so angry, so depressed and so sore that your spirit cannot find deliverance and peace even in prayer, then quickly go and give some pleasure to someone lowly or sorrowful, or to a guilty or innocent sufferer... - Abdu'l-Baha",

        "Sacrifice yourself, your talent, your time, your rest to another, to one who has to bear a heavier load than you. ...and your unhappy mood will dissolve into a blessed, contented submission to God. - Abdu'l-Baha",

        "Man is in reality a spiritual being and only when he lives in the spirit is he truly happy. - Abdu'l-Baha",

        "When a man turns his face to God he finds sunshine everywhere. - Abdu'l-Baha",

        "An humble man without learning, but filled with the Holy Spirit, is more powerful than the most nobly-born profound scholar without that inspiration. - Abdu'l-Baha",

        "It is your duty to be exceedingly kind to every human being...until ye change the world of man into the world of God. - Abdu'l-Baha",

        "The home of Religion is the heart. - Abdu'l-Baha",

        "By nothing,under no conditions, be ye perturbed. - Abdu'l-Baha",
    ];

    $initialMessage = $initialMessages[array_rand($initialMessages)];

// $initialMessage = "Dear beloved friends, You're invited to join us to Celebrate the Birthday of the Twin Manifestations. Date: 6th & 7th Nov 2021. Venue: Baha'i Center, Igando. Time: 10am. There is joy in sharing (refreshments).";

// $initialMessage = "Dear friend, please you're cordially invited to partake in the LSA's meeting today. Time: 12pm, Venue: Regional Center";

    $venue = "Baha'i Center, Igando";
    $time = date('D') == "Fri" ? "10am" : "5pm";
    $date = "18 Jan";

    set_time_limit(0);

    foreach ($allNumbers as $number) {

//        if($msgIsFeastReminder) {
//            $initialMessage = "Give ye great weight to the Nineteen Day gatherings, so that on these occasions the beloved of the Lord and the handmaids of the Merciful may turn their faces toward the Kingdom, chant the communes, beseech God's help, become joyfully enamoured each of the other, and grow in purity and holiness, and in the fear of God, and in resistance to passion and self...";
//        }

//        $url = "https://www.bulksmsnigeria.com/api/v1/sms/create?api_token=vm4kgpPbTqki6jIIkNMUSf1q6xEv7x6plfn8tvwVl7sYFmELv9pgFdKnbbv8&from=Unity&to=$number&body=$initialMessage&dnd=2";
//
//        $response = Http::get($url)->json();
//        dump($response['data']);

         if($msgIsFeastReminder) {
             $initialMessage = "Hi, The LSA of Alimosho cordially invites you to the Baha'i 19-day feast. Date: $date.  Time: $time. Venue: $venue. For more info, pls call/whatsapp 07060961293 / 07068535208&dnd=2";

             $url = "https://www.bulksmsnigeria.com/api/v1/sms/create?api_token=vm4kgpPbTqki6jIIkNMUSf1q6xEv7x6plfn8tvwVl7sYFmELv9pgFdKnbbv8&from=Unity&to=$number&body=$initialMessage&dnd=2";

             $response = Http::get($url)->json();
             if(!empty($response['data'])) {
                 dump($response['data']);
             }
         }
    }
});
