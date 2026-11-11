<?php

/*
 * Static content pages (footer links) and homepage FAQ.
 * Body is HTML. These are starter drafts — replace the policy wording with your final legal text.
 */
return [
    'faqs' => [
        ['What is your return policy?', 'Unused items in their original sealed packaging can be returned within 30 days of delivery. Contact us with your order number and we will arrange the return.'],
        ['How long does shipping take?', 'Orders are dispatched within 1–2 business days. Standard delivery takes 3–5 business days depending on your location.'],
        ['Can I pay cash on delivery?', 'Yes. Cash on delivery is available on all orders. Online payment options will be added soon.'],
        ['Are your products safe for competing athletes?', 'Our formulas are made without banned substances. Always check the label and consult your sports physician or federation rules before use.'],
    ],

    'pages' => [
        'about' => [
            'title' => 'About Powerath',
            'body' => '<p>Powerath — short for <strong>Power Athlete</strong> — makes sports nutrition for people who train seriously. We focus on clean formulas, honest labelling and fast delivery.</p><p>Whether you are building endurance, chasing performance or putting on lean muscle, our range is designed to support the work you put in.</p>',
        ],
        'contact' => [
            'title' => 'Contact us',
            'body' => '<p>Questions about an order or a product? Reach out and we will get back to you within one business day.</p>',
            'contact' => true,
        ],
        'privacy-policy' => [
            'title' => 'Privacy Policy',
            'body' => '<p>We collect only the information needed to process your orders and improve your experience: name, contact details, delivery address and order history. We never sell your personal data.</p><p>You can ask us to update or delete your account data at any time by contacting us.</p>',
        ],
        'terms-of-service' => [
            'title' => 'Terms of Service',
            'body' => '<p>By using this website and placing an order you agree to these terms. Prices and availability may change without notice. We may cancel orders that cannot be fulfilled and will refund any payment made.</p><p>Supplements are not intended to diagnose, treat, cure or prevent any disease. Consult a professional before use.</p>',
        ],
        'shipping-policy' => [
            'title' => 'Shipping Policy',
            'body' => '<p>Orders are dispatched within 1–2 business days. Standard delivery takes 3–5 business days.</p><p>Shipping is free on orders above the free-shipping threshold shown in your cart; a flat fee applies below it. Cash on delivery is available.</p>',
        ],
        'return-policy' => [
            'title' => 'Return Policy',
            'body' => '<p>Unused, unopened items can be returned within 30 days of delivery. Opened supplements cannot be returned for hygiene reasons unless they are faulty or damaged.</p><p>Contact us with your order number to start a return.</p>',
        ],
        'become-a-distributor' => [
            'title' => 'Become a Distributor / Retailer',
            'body' => '<p>Interested in stocking Powerath in your store or gym? Send us your business details and we will share our distributor terms.</p>',
            'contact' => true,
        ],
    ],
];
