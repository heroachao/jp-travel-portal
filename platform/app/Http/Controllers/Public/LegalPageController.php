<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Seo\MetaPayload;
use App\Services\Settings\SiteSettings;
use App\Support\PublicUrl;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function about(SiteSettings $settings): View
    {
        return $this->render('about', $settings);
    }

    public function contact(SiteSettings $settings): View
    {
        return $this->render('contact', $settings);
    }

    public function privacy(SiteSettings $settings): View
    {
        return $this->render('privacy', $settings);
    }

    public function terms(SiteSettings $settings): View
    {
        return $this->render('terms', $settings);
    }

    public function disclaimer(SiteSettings $settings): View
    {
        return $this->render('disclaimer', $settings);
    }

    private function render(string $key, SiteSettings $settings): View
    {
        $site = $settings->current();
        $contactEmail = $site->contact_email ?: 'contact@japantriptools.com';
        $pages = $this->pages($contactEmail);
        $page = $pages[$key];

        return view('public.pages.legal', [
            'meta' => new MetaPayload(
                $page['title'].' | '.$site->site_name,
                $page['description'],
                PublicUrl::route($page['route']),
            ),
            'page' => $page,
            'contactEmail' => $contactEmail,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function pages(string $contactEmail): array
    {
        return [
            'about' => [
                'route' => 'pages.about',
                'title' => 'About Japan Trip Tools',
                'eyebrow' => 'About',
                'description' => 'Learn how Japan Trip Tools publishes independent Japan travel guides, route planning notes, and practical trip resources.',
                'intro' => 'Japan Trip Tools is an independent English-language travel planning site for people researching Japan trips with practical, source-aware information.',
                'updated' => 'June 19, 2026',
                'sections' => [
                    [
                        'heading' => 'What We Publish',
                        'body' => [
                            'We create destination guides, itinerary ideas, transport explainers, seasonal planning notes, food and shopping articles, and travel tool pages for visitors who want clear decisions rather than copied attraction lists.',
                            'Our editorial goal is to help readers compare regions, understand realistic travel times, prepare for weather and transport changes, and plan respectfully around local communities.',
                        ],
                    ],
                    [
                        'heading' => 'Editorial Standards',
                        'body' => [
                            'Articles are written in professional English for international readers. We review facts before publication and update pages when practical details change or when official sources publish newer guidance.',
                            'We may use official tourism information, transport operators, public notices, and carefully selected image sources as references, but this site is not owned by or officially affiliated with the Japanese government, prefectural offices, transport companies, hotels, or attractions.',
                        ],
                    ],
                    [
                        'heading' => 'How The Site Is Funded',
                        'body' => [
                            'Japan Trip Tools may display advertising, sponsored placements, or affiliate links. Advertising helps keep the public guides free to read. Commercial relationships do not give advertisers editorial control over our travel guidance.',
                        ],
                    ],
                ],
            ],
            'contact' => [
                'route' => 'pages.contact',
                'title' => 'Contact Japan Trip Tools',
                'eyebrow' => 'Contact',
                'description' => 'Contact Japan Trip Tools for corrections, editorial questions, advertising inquiries, and site feedback.',
                'intro' => 'Use this page for corrections, editorial questions, advertising inquiries, and feedback about Japan Trip Tools.',
                'updated' => 'June 19, 2026',
                'sections' => [
                    [
                        'heading' => 'Email',
                        'body' => [
                            'General contact: '.$contactEmail,
                            'For corrections, please include the page URL, the statement that needs review, and the official or primary source that supports the change.',
                        ],
                    ],
                    [
                        'heading' => 'Corrections And Updates',
                        'body' => [
                            'Travel information can change quickly. If you find an outdated transport note, seasonal date, facility policy, price range, or safety detail, we will review the source and update the page when appropriate.',
                            'We do not provide emergency assistance, visa decisions, travel agency booking support, or official customer service for third-party businesses.',
                        ],
                    ],
                    [
                        'heading' => 'Advertising',
                        'body' => [
                            'Advertising and partnership questions can be sent to the same contact address. Sponsored opportunities must be clearly identified and cannot require misleading travel claims.',
                        ],
                    ],
                ],
            ],
            'privacy' => [
                'route' => 'pages.privacy',
                'title' => 'Privacy Policy',
                'eyebrow' => 'Privacy',
                'description' => 'Read the Japan Trip Tools privacy policy covering analytics, advertising, cookies, contact messages, and third-party services.',
                'intro' => 'This Privacy Policy explains how Japan Trip Tools handles basic site data, analytics, advertising technologies, and contact messages.',
                'updated' => 'June 19, 2026',
                'sections' => [
                    [
                        'heading' => 'Information We May Collect',
                        'body' => [
                            'When you browse the site, standard server and analytics data may be processed, including pages visited, device and browser type, approximate location, referral source, and time spent on pages.',
                            'If you contact us by email, we may receive your email address, name if provided, message content, and any source materials you choose to send.',
                        ],
                    ],
                    [
                        'heading' => 'Analytics And Advertising',
                        'body' => [
                            'We may use analytics tools to understand which pages are useful, which search terms bring visitors to the site, and how readers move through the site.',
                            'We may use Google AdSense or similar advertising services. Advertising partners may use cookies or similar technologies to serve, measure, and personalize ads according to their own policies and user controls.',
                        ],
                    ],
                    [
                        'heading' => 'Cookies',
                        'body' => [
                            'Cookies may be used for analytics, advertising measurement, security, and basic site functionality. You can control cookies through your browser settings, although some features or ad controls may work differently when cookies are blocked.',
                        ],
                    ],
                    [
                        'heading' => 'Third-Party Links',
                        'body' => [
                            'Our articles may link to official tourism offices, transport operators, booking services, maps, image sources, and other external websites. Those sites have their own privacy practices, and Japan Trip Tools is not responsible for their content or policies.',
                        ],
                    ],
                    [
                        'heading' => 'Contact And Data Questions',
                        'body' => [
                            'For privacy questions or requests related to messages you sent us, contact '.$contactEmail.'. We will make a reasonable effort to review and respond to legitimate requests.',
                        ],
                    ],
                ],
            ],
            'terms' => [
                'route' => 'pages.terms',
                'title' => 'Terms of Use',
                'eyebrow' => 'Terms',
                'description' => 'Read the Japan Trip Tools terms of use for travel information, permitted use, advertising, and external links.',
                'intro' => 'By using Japan Trip Tools, you agree to use the site responsibly and to verify important travel decisions with official or current sources.',
                'updated' => 'June 19, 2026',
                'sections' => [
                    [
                        'heading' => 'Use Of The Site',
                        'body' => [
                            'Japan Trip Tools provides general travel information, planning notes, and editorial recommendations for informational purposes. You are responsible for your own travel decisions, bookings, documents, safety preparations, and local rule compliance.',
                            'You may read and share links to our public pages. You may not scrape, copy, republish, or commercially reuse substantial parts of the site without permission.',
                        ],
                    ],
                    [
                        'heading' => 'Accuracy And Availability',
                        'body' => [
                            'We work to publish useful and accurate information, but travel conditions, schedules, prices, opening hours, weather risks, and local rules can change without notice.',
                            'The site may be updated, reorganized, or temporarily unavailable. We do not guarantee that every page will remain available in the same format.',
                        ],
                    ],
                    [
                        'heading' => 'Advertising And External Services',
                        'body' => [
                            'The site may include advertising, sponsored links, affiliate links, or links to third-party services. A link or ad does not mean we control or endorse every claim, price, product, or policy on the external site.',
                        ],
                    ],
                    [
                        'heading' => 'Contact',
                        'body' => [
                            'Questions about these terms can be sent to '.$contactEmail.'.',
                        ],
                    ],
                ],
            ],
            'disclaimer' => [
                'route' => 'pages.disclaimer',
                'title' => 'Travel Information Disclaimer',
                'eyebrow' => 'Disclaimer',
                'description' => 'Read the Japan Trip Tools disclaimer about travel information, official sources, safety, ads, and third-party services.',
                'intro' => 'Japan Trip Tools is designed to support trip planning, not to replace official sources, professional advice, or real-time travel checks.',
                'updated' => 'June 19, 2026',
                'sections' => [
                    [
                        'heading' => 'Not Official Advice',
                        'body' => [
                            'This site is independent and is not an official government, prefectural, municipal, immigration, railway, airline, hotel, attraction, or emergency service website.',
                            'Before making important travel decisions, check official sources for visas, entry requirements, weather warnings, transport status, closures, prices, and safety notices.',
                        ],
                    ],
                    [
                        'heading' => 'Travel Risk',
                        'body' => [
                            'Japan travel can be affected by earthquakes, typhoons, heavy snow, heat, crowd controls, strikes, maintenance closures, and timetable changes. Always keep flexible plans and follow local instructions.',
                        ],
                    ],
                    [
                        'heading' => 'Images, Ads, And Links',
                        'body' => [
                            'Images are used to illustrate travel ideas and may not show the exact current condition of a destination. Ads and external links may lead to third-party services with their own terms, prices, and availability.',
                        ],
                    ],
                    [
                        'heading' => 'Corrections',
                        'body' => [
                            'If you find information that appears outdated or incorrect, contact '.$contactEmail.' with the page URL and a reliable source for review.',
                        ],
                    ],
                ],
            ],
        ];
    }
}
