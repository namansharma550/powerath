/*
 * AXIOM storefront data.
 * In Laravel, replace this file with a Blade-injected payload, e.g.
 *   <script>window.AXIOM_DATA = @json($storePayload);</script>
 * Keys: products, reviews (from DB) + categoryTiles, articles, brands, policies (static content).
 */
window.AXIOM_DATA = {
  "products": [
    {
      "id": 1,
      "slug": "iso-pure-100-whey-isolate",
      "sku": "AXM-WHY-001",
      "brand": "AXIOM LABS",
      "name": "ISO-PURE 100% Cold-Filtered Whey Isolate",
      "subtitle": "28g Native Protein · 3.1g L-Leucine · <0.5g Lactose",
      "category": "Whey Protein",
      "priceCents": 6400,
      "compareAtPriceCents": 7600,
      "rating": 4.9,
      "reviewCount": 418,
      "badge": "BESTSELLER",
      "imageUrl": "images/prod-whey-isolate.jpg",
      "flavorsJson": [
        "Dutch Reserve Cacao",
        "Madagascar Bean Vanilla",
        "Unflavored Pure"
      ],
      "servingInfo": "30 Servings · 32.5g Scoop (975g Net Wt)",
      "description": "Cross-flow micro-filtered whey protein isolate engineered for rapid digestion and maximal post-training muscle protein synthesis. Zero amino spiking, zero artificial dyes, and third-party assayed for heavy metals and label accuracy.",
      "nutritionFactsJson": [
        {
          "label": "Calories",
          "amount": "120 kcal"
        },
        {
          "label": "Total Fat",
          "amount": "0.5 g",
          "dailyValue": "1%"
        },
        {
          "label": "Total Carbohydrate",
          "amount": "1.0 g",
          "dailyValue": "<1%"
        },
        {
          "label": "Total Sugars",
          "amount": "0 g"
        },
        {
          "label": "Protein (from Whey Isolate)",
          "amount": "28.0 g",
          "dailyValue": "56%"
        },
        {
          "label": "Naturally Occurring L-Leucine",
          "amount": "3,120 mg"
        },
        {
          "label": "Naturally Occurring BCAAs",
          "amount": "6,480 mg"
        },
        {
          "label": "Sodium (from Sea Salt)",
          "amount": "140 mg",
          "dailyValue": "6%"
        }
      ],
      "highlightsJson": [
        "Cold cross-flow micro-filtration preserves native whey fractions",
        "Full amino acid chromatogram published per production lot",
        "Zero gums, creamers, maltodextrin, or proprietary blends"
      ],
      "batchCode": "LOT #AX-2604A · ISO/IEC 17025 VERIFIED",
      "inStock": true,
      "featuredOrder": 1
    },
    {
      "id": 2,
      "slug": "micronized-creatine-monohydrate-5000",
      "sku": "AXM-CRE-002",
      "brand": "CREAPURE® SERIES",
      "name": "Micronized Creatine Monohydrate 5000",
      "subtitle": "99.99% Pure German Creapure® · 200-Mesh Micronized",
      "category": "Creatine",
      "priceCents": 3800,
      "compareAtPriceCents": 4500,
      "rating": 5,
      "reviewCount": 612,
      "badge": "CLINICAL STANDARD",
      "imageUrl": "images/prod-creatine.jpg",
      "flavorsJson": [
        "Unflavored Micronized (90 Servings)",
        "Unflavored Travel Tub (60 Servings)"
      ],
      "servingInfo": "90 Servings · 5.0g Precision Scoop (450g Net Wt)",
      "description": "Single-ingredient pharmaceutical-grade micronized creatine monohydrate sourced from Alzchem Trostberg GmbH in Germany. Supports ATP phosphocreatine regeneration during high-intensity repeated resistance bouts.",
      "nutritionFactsJson": [
        {
          "label": "Calories",
          "amount": "0 kcal"
        },
        {
          "label": "Creapure® Creatine Monohydrate",
          "amount": "5,000 mg",
          "dailyValue": "**"
        },
        {
          "label": "Dicyandiamide (DCD) Impurity",
          "amount": "<10 ppm (Verified)"
        },
        {
          "label": "Dihydrotriazine (DHT)",
          "amount": "Non-Detectable"
        }
      ],
      "highlightsJson": [
        "200-mesh particle micronization for clean suspension in cold water",
        "Single ingredient — zero fillers, sweeteners, or flow agents",
        "HPLC assayed at 99.99% purity per production run"
      ],
      "batchCode": "LOT #CP-9941G · HPLC PURITY CERTIFIED",
      "inStock": true,
      "featuredOrder": 2
    },
    {
      "id": 3,
      "slug": "ignite-ergogenic-pre-workout",
      "sku": "AXM-PRE-003",
      "brand": "AXIOM LABS",
      "name": "IGNITE Clinical Pre-Workout Formula",
      "subtitle": "8g Pure L-Citrulline · 3.2g Beta-Alanine · 200mg Natural Caffeine",
      "category": "Pre-workout",
      "priceCents": 5400,
      "compareAtPriceCents": 6400,
      "rating": 4.8,
      "reviewCount": 289,
      "badge": "FULL DISCLOSURE",
      "imageUrl": "images/prod-preworkout.jpg",
      "flavorsJson": [
        "Blood Orange Tart",
        "Black Cherry Ice",
        "Crisp Yuzu Citrus"
      ],
      "servingInfo": "20 Full Clinical Servings · 19.4g Scoop (388g Net Wt)",
      "description": "Evidence-dosed pre-training formula built strictly around peer-reviewed ergogenic dosages. Delivers nitric oxide vasodilation support, intracellular carnosine buffering, and measured focus without jittery over-stimulation.",
      "nutritionFactsJson": [
        {
          "label": "L-Citrulline (Fermented, Free-Form)",
          "amount": "8,000 mg",
          "dailyValue": "**"
        },
        {
          "label": "Beta-Alanine (CarnoSyn® Grade)",
          "amount": "3,200 mg",
          "dailyValue": "**"
        },
        {
          "label": "Betaine Anhydrous",
          "amount": "2,500 mg",
          "dailyValue": "**"
        },
        {
          "label": "L-Tyrosine",
          "amount": "1,500 mg",
          "dailyValue": "**"
        },
        {
          "label": "Organic Green Coffee Caffeine",
          "amount": "200 mg",
          "dailyValue": "**"
        },
        {
          "label": "L-Theanine",
          "amount": "100 mg",
          "dailyValue": "**"
        },
        {
          "label": "Pink Himalayan Sodium",
          "amount": "200 mg",
          "dailyValue": "9%"
        }
      ],
      "highlightsJson": [
        "100% open label — zero proprietary stimulant matrices",
        "Fermented pure L-Citrulline rather than diluted 2:1 malate",
        "Screened for WADA-prohibited stimulants in accredited labs"
      ],
      "batchCode": "LOT #AX-2611P · BANNED-SUBSTANCE SCREENED",
      "inStock": true,
      "featuredOrder": 3
    },
    {
      "id": 4,
      "slug": "hydrate-eaa-electrolyte-recovery",
      "sku": "KIN-REC-004",
      "brand": "KINETIC HYDRATION",
      "name": "HYDRATE+ Full-Spectrum EAA & Electrolytes",
      "subtitle": "9g Essential Amino Acids · 1,000mg Sodium · Potassium & Magnesium",
      "category": "Recovery",
      "priceCents": 4600,
      "compareAtPriceCents": 5400,
      "rating": 4.9,
      "reviewCount": 194,
      "badge": "INTRA / POST",
      "imageUrl": "images/prod-electrolytes.jpg",
      "flavorsJson": [
        "Salted Citrus Lime",
        "Wild Berry Mineral",
        "Raw Lemon Peel"
      ],
      "servingInfo": "30 Servings · 15.2g Scoop (456g Net Wt)",
      "description": "Isotonic intra- and post-training hydration matrix combining all nine essential amino acids with precision sweat-replacement electrolytes to maintain neuromuscular output and accelerate cellular rehydration.",
      "nutritionFactsJson": [
        {
          "label": "Total Essential Amino Acids (9 EAAs)",
          "amount": "9,000 mg",
          "dailyValue": "**"
        },
        {
          "label": "Sodium (Sodium Citrate & Sea Salt)",
          "amount": "1,000 mg",
          "dailyValue": "43%"
        },
        {
          "label": "Potassium (Potassium Citrate)",
          "amount": "250 mg",
          "dailyValue": "5%"
        },
        {
          "label": "Magnesium (Magnesium Malate)",
          "amount": "80 mg",
          "dailyValue": "19%"
        },
        {
          "label": "Coconut Water Freeze-Dried Powder",
          "amount": "500 mg",
          "dailyValue": "**"
        }
      ],
      "highlightsJson": [
        "Engineered for high-sweat conditioning and two-a-day training blocks",
        "Chelated magnesium malate for gentle gastrointestinal tolerance",
        "Zero added sugar, zero artificial colors"
      ],
      "batchCode": "LOT #KH-8820R · OSMOLALITY VERIFIED",
      "inStock": true,
      "featuredOrder": 4
    },
    {
      "id": 5,
      "slug": "vital-d3-k2-magnesium-complex",
      "sku": "OPT-VIT-005",
      "brand": "OPTIMAL FORM",
      "name": "VITAL Performance Micronutrient Complex",
      "subtitle": "Vitamin D3 4000 IU · MenaQ7® K2 · Chelated Zinc & Magnesium Bisglycinate",
      "category": "Vitamins & Minerals",
      "priceCents": 4200,
      "compareAtPriceCents": null,
      "rating": 4.8,
      "reviewCount": 167,
      "badge": "DAILY FOUNDATION",
      "imageUrl": "images/prod-vitamins.jpg",
      "flavorsJson": [
        "90 Vegetable Capsules (30-Day Supply)"
      ],
      "servingInfo": "30 Servings · 3 Capsules Daily with Meal",
      "description": "Bioavailable micronutrient foundation formulated specifically for strength and endurance athletes facing elevated mineral turnover. Uses chelated Albion® minerals and active MenaQ7® MK-7.",
      "nutritionFactsJson": [
        {
          "label": "Vitamin D3 (Cholecalciferol, Lichen)",
          "amount": "100 mcg (4,000 IU)",
          "dailyValue": "500%"
        },
        {
          "label": "Vitamin K2 (as MenaQ7® MK-7)",
          "amount": "120 mcg",
          "dailyValue": "100%"
        },
        {
          "label": "Magnesium (as TRAACS® Bisglycinate)",
          "amount": "250 mg",
          "dailyValue": "60%"
        },
        {
          "label": "Zinc (as Zinc Bisglycinate Chelate)",
          "amount": "15 mg",
          "dailyValue": "136%"
        },
        {
          "label": "Boron (as Bororganic Glycine)",
          "amount": "3 mg",
          "dailyValue": "**"
        }
      ],
      "highlightsJson": [
        "Chelated mineral forms for superior absorption over oxide salts",
        "Plant-sourced cholecalciferol paired with trans-isomer K2 MK-7",
        "Dark UV-protective apothecary bottle preserves potency"
      ],
      "batchCode": "LOT #OF-3310V · USP DISSOLUTION TESTED",
      "inStock": true,
      "featuredOrder": 5
    },
    {
      "id": 6,
      "slug": "10mm-ipf-spec-powerlifting-lever-belt",
      "sku": "TRQ-ACC-006",
      "brand": "TORQUE STRENGTH",
      "name": "10mm Competition Leather Lever Belt",
      "subtitle": "Vegetable-Tanned Full-Grain Hide · Matte Gunmetal Aircraft Steel Lever",
      "category": "Gym Accessories",
      "priceCents": 12800,
      "compareAtPriceCents": 14800,
      "rating": 4.9,
      "reviewCount": 231,
      "badge": "LIFETIME LEVER",
      "imageUrl": "images/prod-belt.jpg",
      "flavorsJson": [
        "Small (26\"–31\")",
        "Medium (30\"–35\")",
        "Large (34\"–39\")",
        "XL (38\"–44\")"
      ],
      "servingInfo": "10mm Thickness · 10cm Uniform Competition Width",
      "description": "Built to international powerlifting technical dimensions (10cm width, 10mm thickness) from vegetable-tanned full-grain cowhide with a precision-milled matte black steel quick-release lever.",
      "nutritionFactsJson": [
        {
          "label": "Belt Width",
          "amount": "10.0 cm (Uniform)"
        },
        {
          "label": "Core Thickness",
          "amount": "10.0 mm (+/- 0.3 mm)"
        },
        {
          "label": "Leather Hide",
          "amount": "100% Vegetable-Tanned Full-Grain"
        },
        {
          "label": "Buckle Hardware",
          "amount": "CNC Cast Steel Lever · Matte PVD"
        },
        {
          "label": "Stitching",
          "amount": "6-Ply Bonded Nylon · Crimson Lockstitch"
        }
      ],
      "highlightsJson": [
        "Compliant with IPF, USAPL, and USPA 10cm/10mm technical rules",
        "Pre-conditioned interior suede liner reduces break-in period",
        "Lifetime mechanical replacement warranty on lever assembly"
      ],
      "batchCode": "SPEC #TQ-10MM-BLK · INSPECTED",
      "inStock": true,
      "featuredOrder": 6
    },
    {
      "id": 7,
      "slug": "750ml-insulated-steel-pro-shaker",
      "sku": "AXM-ACC-007",
      "brand": "AXIOM LABS",
      "name": "750ml Double-Wall Steel Training Shaker",
      "subtitle": "304 Food-Grade Stainless · Internal Wire Whisk · Leakproof Twist Lock",
      "category": "Gym Accessories",
      "priceCents": 3200,
      "compareAtPriceCents": 3800,
      "rating": 4.8,
      "reviewCount": 305,
      "badge": "ODOR-RESISTANT",
      "imageUrl": "images/prod-shaker.jpg",
      "flavorsJson": [
        "Matte Carbon Black / Signal Red Loop"
      ],
      "servingInfo": "750 ml / 25 fl oz · Laser-Etched Internal Scale",
      "description": "Vacuum-insulated 18/8 medical-grade stainless steel shaker engineered to eliminate plastic odor retention while keeping post-workout isolates ice-cold for up to 18 hours.",
      "nutritionFactsJson": [
        {
          "label": "Vessel Material",
          "amount": "18/8 (304) Stainless Steel"
        },
        {
          "label": "Total Capacity",
          "amount": "750 ml / 25 oz"
        },
        {
          "label": "Thermal Rating",
          "amount": "18 Hours Cold Insulation"
        },
        {
          "label": "BPA / Phthalate Status",
          "amount": "100% Free"
        }
      ],
      "highlightsJson": [
        "Silent snap-in surgical steel agitator grate — no rattling ball",
        "Silicone gasket quarter-turn lid tested against gym-bag leaks",
        "Internal laser-engraved 100ml–700ml dosing graduations"
      ],
      "batchCode": "SPEC #AX-SHK-750 · FOOD-GRADE CERTIFIED",
      "inStock": true,
      "featuredOrder": 7
    },
    {
      "id": 8,
      "slug": "heavy-duty-wrist-wraps-lifting-straps-kit",
      "sku": "TRQ-ACC-008",
      "brand": "TORQUE STRENGTH",
      "name": "Heavy-Duty 24\" Wrist Wraps & Lifting Straps Kit",
      "subtitle": "Competition Elastic Press Wraps + Cotton Webbing Pull Straps",
      "category": "Gym Accessories",
      "priceCents": 3600,
      "compareAtPriceCents": 4400,
      "rating": 4.9,
      "reviewCount": 178,
      "badge": "PRESS & PULL KIT",
      "imageUrl": "images/prod-wraps.jpg",
      "flavorsJson": [
        "24\" Stiff Competition Kit",
        "18\" Hybrid Training Kit"
      ],
      "servingInfo": "1 Pair Wrist Wraps + 1 Pair Cotton Lifting Straps",
      "description": "Matched strength accessory kit pairing stiff 24-inch thumb-loop wrist wraps for heavy bench and overhead pressing with dense neoprene-padded cotton deadlift straps.",
      "nutritionFactsJson": [
        {
          "label": "Wrist Wrap Dimensions",
          "amount": "24 in × 3 in (60 cm × 8 cm)"
        },
        {
          "label": "Wrap Blend",
          "amount": "70% Woven Cotton / 30% Heavy Elastic"
        },
        {
          "label": "Lifting Strap Length",
          "amount": "21.5 in Heavy Cotton Webbing"
        },
        {
          "label": "Tensile Load Rating",
          "amount": "Tested to 350 kg / 770 lbs"
        }
      ],
      "highlightsJson": [
        "Extra-wide 2-inch hook-and-loop closure resists shear under max loads",
        "Reinforced left/right ergonomic thumb loops",
        "Abrasion-resistant kevlar-blend box stitching on pull straps"
      ],
      "batchCode": "SPEC #TQ-KIT-24 · TENSILE TESTED",
      "inStock": true,
      "featuredOrder": 8
    },
    {
      "id": 9,
      "slug": "hydro-whey-post-workout-recovery-matrix",
      "sku": "KIN-REC-009",
      "brand": "VERITAS SPORT",
      "name": "REBUILD Post-Training Carb & Hydro-Isolate",
      "subtitle": "25g Hydrolyzed Whey + 30g Cluster Dextrin® · 3:1 Glycogen Ratio",
      "category": "Recovery",
      "priceCents": 6800,
      "compareAtPriceCents": 7800,
      "rating": 4.9,
      "reviewCount": 142,
      "badge": "GLYCOGEN RELOAD",
      "imageUrl": "images/prod-whey-isolate.jpg",
      "flavorsJson": [
        "Dark Chocolate Malt",
        "Salted Caramel Espresso"
      ],
      "servingInfo": "20 Servings · 60g Post-Session Scoop (1.2kg Net Wt)",
      "description": "Formulated for high-volume strength blocks, combat athletes, and endurance sessions requiring rapid hepatic and muscular glycogen replenishment alongside hydrolyzed whey peptides.",
      "nutritionFactsJson": [
        {
          "label": "Calories",
          "amount": "225 kcal"
        },
        {
          "label": "Total Carbohydrate (Cluster Dextrin®)",
          "amount": "30.0 g",
          "dailyValue": "11%"
        },
        {
          "label": "Hydrolyzed Whey Protein Isolate",
          "amount": "25.0 g",
          "dailyValue": "50%"
        },
        {
          "label": "Tart Cherry Skin Extract (VitaCherry®)",
          "amount": "480 mg",
          "dailyValue": "**"
        },
        {
          "label": "Sodium",
          "amount": "220 mg",
          "dailyValue": "10%"
        }
      ],
      "highlightsJson": [
        "Highly Branched Cyclic Dextrin empties rapidly with minimal gastric bloat",
        "Standardized Montmorency tart cherry extract for recovery support",
        "Third-party screened for athletic compliance"
      ],
      "batchCode": "LOT #VS-5502R · ISO-17025 VERIFIED",
      "inStock": true,
      "featuredOrder": 9
    }
  ],
  "reviews": [
    {
      "id": 1,
      "productId": 1,
      "productName": "ISO-PURE 100% Cold-Filtered Whey Isolate",
      "authorName": "Marcus Vance, CSCS",
      "athleteRole": "Division I Strength & Conditioning Coach",
      "rating": 5,
      "title": "Zero gastric distress and verifiable amino disclosure.",
      "body": "Most isolates hide behind thickeners and proprietary protein blends. AXIOM publishes the actual lot-level amino profile and mixes dead-clean in cold water within five seconds. Dutch Reserve Cacao tastes clean, not cloying.",
      "verifiedPurchase": true
    },
    {
      "id": 2,
      "productId": 2,
      "productName": "Micronized Creatine Monohydrate 5000",
      "authorName": "Elena Rostova",
      "athleteRole": "Competitive 69kg Powerlifter",
      "rating": 5,
      "title": "True 200-mesh Creapure — zero grit at the bottom of the shaker.",
      "body": "I have taken 5g of creatine daily for six years. The particle fineness on this Creapure batch is noticeably superior to standard bulk monohydrate, and knowing every lot is HPLC-assayed gives complete peace of mind leading into drug-tested meets.",
      "verifiedPurchase": true
    },
    {
      "id": 3,
      "productId": 3,
      "productName": "IGNITE Clinical Pre-Workout Formula",
      "authorName": "Devon K. Brooks",
      "athleteRole": "Hybrid Marathon & Barbell Athlete",
      "rating": 5,
      "title": "Measured 200mg focus without wrecking post-session heart rate.",
      "body": "Finally a pre-workout formulated for serious training rather than 400mg caffeine shock value. Full 8g pure L-Citrulline delivers unmistakable work-capacity endurance through heavy 5x5 squat blocks with zero crash.",
      "verifiedPurchase": true
    },
    {
      "id": 4,
      "productId": 6,
      "productName": "10mm Competition Leather Lever Belt",
      "authorName": "Callum Reid",
      "athleteRole": "Raw 105kg Strength Athlete",
      "rating": 5,
      "title": "Rock-solid intra-abdominal bracing right out of the box.",
      "body": "The 10mm vegetable-tanned hide molds around the ribs in three heavy sessions while keeping rigid lumbar support under 260kg pulls. The matte gunmetal lever locks flush with zero play.",
      "verifiedPurchase": true
    }
  ],
  "categoryTiles": [
    {
      "id": "Whey Protein",
      "code": "CAT // 01",
      "title": "Whey Protein",
      "tagline": "Cold-filtered micro-isolates with verified leucine content for lean synthesis.",
      "metric": "28G NATIVE PROTEIN",
      "imageUrl": "images/prod-whey-isolate.jpg"
    },
    {
      "id": "Creatine",
      "code": "CAT // 02",
      "title": "Creatine",
      "tagline": "99.99% pure German Creapure® monohydrate for explosive ATP output.",
      "metric": "5,000MG CLINICAL DOSE",
      "imageUrl": "images/prod-creatine.jpg"
    },
    {
      "id": "Pre-workout",
      "code": "CAT // 03",
      "title": "Pre-workout",
      "tagline": "Open-label nitric oxide, carnosine buffering & precision ergogenic focus.",
      "metric": "8G PURE L-CITRULLINE",
      "imageUrl": "images/prod-preworkout.jpg"
    },
    {
      "id": "Vitamins & Minerals",
      "code": "CAT // 04",
      "title": "Vitamins & Minerals",
      "tagline": "Chelated micronutrients engineered for elevated athletic mineral turnover.",
      "metric": "TRAACS® CHELATES",
      "imageUrl": "images/prod-vitamins.jpg"
    },
    {
      "id": "Recovery",
      "code": "CAT // 05",
      "title": "Recovery",
      "tagline": "Full-spectrum EAAs, cyclic dextrin & high-sodium sweat electrolytes.",
      "metric": "1,000MG SODIUM MATRIX",
      "imageUrl": "images/prod-electrolytes.jpg"
    },
    {
      "id": "Gym Accessories",
      "code": "CAT // 06",
      "title": "Gym Accessories",
      "tagline": "IPF-spec 10mm leather lever belts, heavy press wraps & insulated steel shakers.",
      "metric": "COMPETITION GRADE",
      "imageUrl": "images/prod-belt.jpg"
    }
  ],
  "articles": [
    {
      "id": "protein-leucine-kinetics",
      "category": "PROTEIN EDUCATION",
      "readTime": "5 MIN BRIEFING",
      "title": "Whey Isolate vs. Concentrate: Leucine Thresholds & Absorption Kinetics",
      "excerpt": "How cross-flow micro-filtration removes lactose and lipid fractions while preserving the 3.0g L-Leucine trigger required for maximal muscle protein synthesis.",
      "imageUrl": "https://images.pexels.com/photos/19132573/pexels-photo-19132573.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=627&w=1200",
      "keyTakeaways": [
        "Aim for 0.4g–0.55g of protein per kg of bodyweight across 4 evenly spaced feedings.",
        "Each post-resistance feeding should supply at least 2.5g–3.0g of L-Leucine to saturate mTOR signaling.",
        "Cold-filtered whey isolate delivers >90% protein by dry weight with <0.5g lactose per scoop."
      ],
      "sections": [
        {
          "heading": "1. The Leucine Trigger Hypothesis",
          "content": "Skeletal muscle hypertrophy relies on net positive protein balance. Peer-reviewed tracer studies demonstrate that stimulating maximal myofibrillar protein synthesis requires crossing an intracellular L-Leucine threshold of roughly 2.7g to 3.1g per meal. Below this threshold, anabolic signaling remains sub-optimal regardless of total calorie intake."
        },
        {
          "heading": "2. Why Filtration Temperature Matters",
          "content": "Acid- or high-heat-processed whey denatures fragile native fractions like lactoferrin and glycomacropeptides. Cold cross-flow ceramic micro-filtration mechanically separates fat globules and lactose sugar without damaging bioavailability, yielding rapid plasma amino acid elevation within 35–45 minutes."
        },
        {
          "heading": "3. Practical Daily Intake Protocol",
          "content": "Resistance-trained athletes perform best consuming 1.6g to 2.2g of total daily protein per kilogram of body mass. Use whole-food proteins at primary meals and deploy 1 scoop (28g protein) of ISO-PURE Whey Isolate immediately post-training or between long work blocks."
        }
      ],
      "recommendedCategory": "Whey Protein"
    },
    {
      "id": "creatine-saturation-protocol",
      "category": "CREATINE SCIENCE",
      "readTime": "4 MIN BRIEFING",
      "title": "Creatine Monohydrate Saturation: Daily 5g Protocol Without Loading Myths",
      "excerpt": "Why steady daily 5,000mg dosing achieves complete intramuscular phosphocreatine saturation without water-bloat loading phases or exotic ester forms.",
      "imageUrl": "https://images.pexels.com/photos/17944268/pexels-photo-17944268.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=627&w=1200",
      "keyTakeaways": [
        "Creatine monohydrate remains the most extensively validated legal ergogenic aid in sports science.",
        "A consistent 5g daily dose achieves 100% intramuscular saturation within 21–28 days without GI upset.",
        "Intracellular water retention inside muscle fibers supports cell hydration and mechanical leverage."
      ],
      "sections": [
        {
          "heading": "1. The ATP-PCr Energy System Under Heavy Iron",
          "content": "During maximal sets lasting 3 to 15 seconds—such as heavy triples on squats or repeated sprint intervals—your muscles cleave adenosine triphosphate (ATP) rapidly. Intramuscular phosphocreatine donates a phosphate group to regenerate ATP instantaneously, allowing 1–2 additional quality reps per working set."
        },
        {
          "heading": "2. Skip the 20g Loading Phase",
          "content": "While taking 20g/day for 5 days saturates stores faster, high acute boluses frequently draw water into the GI tract. A disciplined 5g daily scoop reaches the exact same saturation ceiling in 3–4 weeks with zero gastrointestinal discomfort. Consistency matters far more than timing."
        },
        {
          "heading": "3. Purity & Particle Size",
          "content": "Alternative forms like buffered creatine or creatine HCl have never outperformed pure monohydrate in controlled trials. Choose HPLC-verified 200-mesh micronized Creapure® to eliminate synthesis byproducts like dicyandiamide."
        }
      ],
      "recommendedCategory": "Creatine"
    },
    {
      "id": "pre-workout-ergogenic-timing",
      "category": "WORKOUT NUTRITION",
      "readTime": "6 MIN BRIEFING",
      "title": "Pre-Workout Ergogenics: L-Citrulline, Beta-Alanine & Caffeine Timing",
      "excerpt": "Separating clinical performance compounds from high-stimulant marketing: exact milligram benchmarks and 30-minute plasma peak timing.",
      "imageUrl": "https://images.pexels.com/photos/1552249/pexels-photo-1552249.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=627&w=1200",
      "keyTakeaways": [
        "Ingest pre-workout 25–35 minutes prior to your first warm-up barbell set.",
        "Look for 6,000mg–8,000mg of pure L-Citrulline rather than underdosed Citrulline Malate blends.",
        "Moderate caffeine (3mg/kg or ~200mg) paired with L-Theanine sharpens bar velocity without sympathetic burnout."
      ],
      "sections": [
        {
          "heading": "1. Nitric Oxide & Pure L-Citrulline",
          "content": "Oral L-Citrulline bypasses hepatic first-pass metabolism far more efficiently than oral L-Arginine, converting to arginine in the kidneys to drive endothelial nitric oxide synthesis. Full 8g clinical doses support blood flow and reduce perceived exertion across high-volume sessions."
        },
        {
          "heading": "2. Hydrogen Ion Buffering with Beta-Alanine",
          "content": "High-rep sets (30–90 seconds time-under-tension) accumulate hydrogen ions that drop intramuscular pH. Daily intake of 3.2g Beta-Alanine elevates muscle carnosine reserves, acting as an intracellular acid buffer."
        },
        {
          "heading": "3. Protecting Sleep Architecture",
          "content": "Caffeine has an average half-life of 5 hours. Afternoon lifters training after 4:00 PM should cap caffeine around 200mg or switch to non-stimulant electrolyte + EAA hydration so deep slow-wave recovery sleep remains uncompromised."
        }
      ],
      "recommendedCategory": "Pre-workout"
    },
    {
      "id": "recovery-electrolytes-glycogen",
      "category": "RECOVERY & GUIDES",
      "readTime": "5 MIN BRIEFING",
      "title": "Post-Training Glycogen Resynthesis, Sodium Osmolality & Sleep Recovery",
      "excerpt": "Why drinking plain water after heavy sweat sessions dilutes plasma sodium—and how targeted sodium, magnesium, and cyclic carbs restore readiness.",
      "imageUrl": "https://images.pexels.com/photos/2261481/pexels-photo-2261481.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=627&w=1200",
      "keyTakeaways": [
        "Athletes lose between 800mg and 1,500mg of sodium per liter of sweat during intense sessions.",
        "A 2% drop in body mass from dehydration measurably impairs maximal isometric and dynamic force.",
        "Evening magnesium bisglycinate supports neuromuscular relaxation and restorative sleep quality."
      ],
      "sections": [
        {
          "heading": "1. Precision Sweat Replacement",
          "content": "Consuming large volumes of plain hypotonic water post-workout triggers renal diuresis before cellular fluid compartments actually rehydrate. Supplying 500mg–1,000mg of sodium alongside potassium citrate restores extracellular osmolality and plasma volume."
        },
        {
          "heading": "2. Two-A-Day Glycogen Windows",
          "content": "When training sessions are separated by fewer than 24 hours, combining 0.8g/kg of low-osmolality carbohydrates (such as Highly Branched Cyclic Dextrin) with 25g+ hydrolyzed whey accelerates GLUT4-mediated glycogen storage."
        },
        {
          "heading": "3. Micronutrient Turnover in Strength Athletes",
          "content": "Heavy resistance training increases urinary and sweat losses of zinc and magnesium. Replenishing chelated bisglycinate forms alongside Vitamin D3 and K2 maintains bone-tendon integrity and immune resilience across peaking blocks."
        }
      ],
      "recommendedCategory": "Recovery"
    }
  ],
  "brands": [
    {
      "name": "AXIOM LABS",
      "tag": "CLINICAL FORMULATIONS"
    },
    {
      "name": "CREAPURE® SERIES",
      "tag": "GERMAN MONOHYDRATE"
    },
    {
      "name": "KINETIC HYDRATION",
      "tag": "ISOTONIC MATRICES"
    },
    {
      "name": "TORQUE STRENGTH",
      "tag": "IPF-SPEC HARDWARE"
    },
    {
      "name": "OPTIMAL FORM",
      "tag": "CHELATED MICRONUTRIENTS"
    },
    {
      "name": "VERITAS SPORT",
      "tag": "TESTED RECOVERY"
    }
  ],
  "policies": {
    "shipping": {
      "title": "Shipping & Fulfillment Policy",
      "updated": "Effective January 2026",
      "paragraphs": [
        "Same-Day Dispatch Cutoff: All verified in-stock orders placed Monday through Friday prior to 2:00 PM EST are picked, lot-scanned, and dispatched the same business day from our climate-controlled fulfillment center.",
        "Complimentary Express Shipping: Domestic orders totaling $75.00 USD or more (after promotional discounts) automatically qualify for complimentary tracked 2–3 business day express shipping. Orders under $75.00 ship at a flat $6.95 carrier rate.",
        "Heat & Seal Protection: All powdered supplements and encapsulated micronutrients ship in tamper-evident induction-sealed containers inside insulated corrugated packaging to safeguard product stability in transit."
      ]
    },
    "returns": {
      "title": "30-Day Return & Quality Guarantee",
      "updated": "Effective January 2026",
      "paragraphs": [
        "Unopened Merchandise: Unopened, factory-sealed nutrition containers and unused gym equipment in original packaging may be returned within 30 calendar days of delivery for a full refund to the original payment method.",
        "First-Scoop Flavor Assurance: If you open an AXIOM LABS flavored supplement for the first time and are unsatisfied with the flavor profile (with at least 85% of product remaining), contact Athlete Support within 14 days for a one-time flavor exchange credit.",
        "Damaged or Defective Shipments: Inspect parcels upon arrival. If tamper seals are compromised in transit, submit your order number and lot photo to support@axiomnutrition.com within 72 hours for immediate complimentary replacement."
      ]
    },
    "privacy": {
      "title": "Privacy & Data Protection Policy",
      "updated": "Effective January 2026",
      "paragraphs": [
        "Data Minimization: AXIOM Sports Nutrition collects only the customer details strictly required to fulfill orders, verify batch warranties, deliver opt-in training briefings, and provide customer support.",
        "Zero Third-Party Data Brokerage: We never sell, rent, or trade your email address, order history, or training profile to external data brokers or ad networks.",
        "Payment Security: Checkout transactions are protected via TLS 1.3 transport encryption and processed through PCI-DSS Level 1 compliant gateways."
      ]
    },
    "terms": {
      "title": "Terms & Conditions and Supplement Compliance",
      "updated": "Effective January 2026",
      "paragraphs": [
        "Lawful Dietary Supplements & Fitness Merchandise Only: All nutritional products sold by AXIOM Sports Nutrition are lawful dietary supplements regulated under DSHEA (21 U.S.C. § 321(ff)) or permitted non-medical athletic training equipment. We strictly prohibit and do not sell anabolic steroids, SARMs, prescription-only pharmaceuticals, or controlled performance-enhancing drugs.",
        "FDA & Medical Disclaimer: Statements on this website have not been evaluated by the Food and Drug Administration. Products are not intended to diagnose, treat, cure, or prevent any disease. Always consult a licensed physician before starting any dietary supplement or high-intensity exercise regimen, particularly if pregnant, nursing, under 18, taking medication, or managing a medical condition.",
        "Lot Verification & Pricing: Product prices are displayed in USD. Promotional codes (such as EARN15) apply to eligible merchandise subtotals and cannot be retroactively combined with wholesale team contracts."
      ]
    }
  }
};
