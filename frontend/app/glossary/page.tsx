import Link from "next/link";

type Term = {
  id: string;
  name: string;
  reading: string;
  formula: string;
  meaning: string;
  rule: string;
  caveat?: string;
};

const CATEGORIES: { title: string; terms: Term[] }[] = [
  {
    title: "収益性（稼ぐ力）",
    terms: [
      {
        id: "roe",
        name: "ROE（自己資本利益率）",
        reading: "Return On Equity",
        formula: "当期純利益 ÷ 自己資本 × 100",
        meaning: "株主が出したお金（自己資本）に対して、どれだけ効率よく利益を生み出せているかを示す指標。",
        rule: "目安: 10%以上で良好、15%以上は優秀とされることが多い。",
        caveat: "借入（負債）が多い企業ほどROEは高く出やすい（レバレッジ効果）。ROEだけで判断せず、ROAや自己資本比率と合わせて見るのが安全。",
      },
      {
        id: "roa",
        name: "ROA（総資産利益率）",
        reading: "Return On Assets",
        formula: "当期純利益 ÷ 総資産 × 100",
        meaning: "借入も含めた「会社が持つ資産全体」に対して、どれだけ利益を生み出せているかを示す指標。負債の影響を受けにくい。",
        rule: "目安: 5%以上で良好、10%以上は優秀。業種によって水準が大きく異なる（製造業は低め、サービス業は高めが一般的）。",
        caveat: "ROEが高くてもROAが低い場合、借入に頼って利益を押し上げている可能性がある。",
      },
      {
        id: "operating-margin",
        name: "営業利益率",
        reading: "Operating Profit Margin",
        formula: "営業利益 ÷ 売上高 × 100",
        meaning: "本業でどれだけ効率よく稼げているかを示す指標。販管費や原価のコントロール力が反映される。",
        rule: "目安: 業種によるが、10%を超えると本業の収益力は高いとされる。",
      },
    ],
  },
  {
    title: "割安性（今の株価は割安か）",
    terms: [
      {
        id: "per",
        name: "PER（株価収益率）",
        reading: "Price Earnings Ratio",
        formula: "株価 ÷ EPS（一株当たり利益）",
        meaning: "今の利益水準に対して株価が何倍まで買われているかを示す指標。低いほど「割安」とされる。",
        rule: "目安: 15倍以下で割安、20〜30倍は標準的、30倍超は成長期待が高く織り込まれている状態。",
        caveat: "成長期待が高い企業は高PERでも正当化されることがあり、業種平均との比較が重要。単独の数値だけで割高・割安と決めつけない。",
      },
      {
        id: "pbr",
        name: "PBR（株価純資産倍率）",
        reading: "Price Book-value Ratio",
        formula: "株価 ÷ BPS（一株当たり純資産）",
        meaning: "会社の解散価値（純資産）に対して株価が何倍まで買われているかを示す指標。",
        rule: "目安: 1倍未満は理論上「解散価値より株価が安い」状態。1倍前後が標準、2倍を超えると成長期待込みの評価。",
      },
      {
        id: "dividend-yield",
        name: "配当利回り",
        reading: "Dividend Yield",
        formula: "一株配当 ÷ 株価 × 100",
        meaning: "今の株価に対して、配当金だけで年何%のリターンが得られるかを示す指標。",
        rule: "目安: 3%を超えると高配当とされることが多い（市場平均は概ね2%前後で推移）。",
        caveat: "配当利回りが極端に高い場合、業績悪化や株価急落による見かけ上の数値の可能性がある。減配リスクも要確認。",
      },
      {
        id: "payout-ratio",
        name: "配当性向",
        reading: "Dividend Payout Ratio",
        formula: "一株配当 ÷ EPS（一株当たり利益） × 100",
        meaning: "稼いだ利益のうち、どれだけを配当として株主に還元しているかを示す指標。",
        rule: "目安: 30%前後が一般的。極端に高い（80%超など）場合、利益に対して配当負担が重く、減配リスクに注意。",
      },
    ],
  },
  {
    title: "財務健全性（潰れにくさ）",
    terms: [
      {
        id: "equity-ratio",
        name: "自己資本比率",
        reading: "Equity Ratio",
        formula: "自己資本 ÷ 総資産 × 100",
        meaning: "総資産のうち、返済不要の自己資本（株主の持ち分）がどれだけの割合を占めるかを示す指標。高いほど借入への依存度が低く、財務が安定しているとされる。",
        rule: "目安: 40%以上で健全、50%以上は特に安定的。20%を下回ると借入依存が高く、財務リスクに注意が必要。",
      },
    ],
  },
  {
    title: "成長性（伸びているか）",
    terms: [
      {
        id: "yoy",
        name: "前年比（YoY）",
        reading: "Year over Year",
        formula: "（今期の値 − 前期の値） ÷ 前期の値 × 100",
        meaning: "売上高・利益などが前年と比べてどれだけ増減したかを示す、最も基本的な成長性の指標。",
        rule: "プラスであれば成長、マイナスであれば減収・減益。単年の変動が大きい業種もあるため、複数年の推移で見るのが望ましい。",
      },
      {
        id: "cagr",
        name: "CAGR（年平均成長率）",
        reading: "Compound Annual Growth Rate",
        formula: "(最終年の値 ÷ 開始年の値)^(1 ÷ 経過年数) − 1 × 100",
        meaning: "複数年にわたる成長を「年平均何%で伸びてきたか」に均して表す指標。単年の前年比よりも一時的な変動に振り回されにくい。",
        rule: "目安: 5%以上で堅調な成長、10%以上は高成長企業とされることが多い。",
        caveat: "計算に使う開始年と終了年の選び方次第で数値が変わる（例えば開始年がたまたま業績の谷だと高く出やすい）。",
      },
    ],
  },
  {
    title: "基礎データ",
    terms: [
      {
        id: "eps",
        name: "EPS（一株当たり利益）",
        reading: "Earnings Per Share",
        formula: "当期純利益 ÷ 発行済株式数",
        meaning: "1株が生み出す利益額。PERの計算や、株数の異なる企業同士の収益力比較に使われる。",
        rule: "—（絶対水準よりも、前年からの伸びやPER計算の材料として見る指標）",
      },
      {
        id: "bps",
        name: "BPS（一株当たり純資産）",
        reading: "Book-value Per Share",
        formula: "純資産 ÷ 発行済株式数",
        meaning: "1株あたりの会社の純資産（解散価値）。PBRの計算に使われる。",
        rule: "—（PBR計算の材料として見る指標）",
      },
    ],
  },
];

export default function GlossaryPage() {
  return (
    <>
      <div className="mb-3">
        <Link href="/stocks" className="text-sm text-slate-500 hover:text-indigo-600">
          &larr; 銘柄一覧へ
        </Link>
      </div>

      <h1 className="text-2xl font-bold text-slate-900 mb-2">指標の見方</h1>
      <p className="text-sm text-slate-500 mb-6 max-w-2xl leading-relaxed">
        このサイトで使っている財務指標の意味と、大まかな目安をまとめています。ここに書かれている「目安」は一般的によく言われる水準であり、業種や成長ステージによって適正水準は大きく異なります。
        <span className="font-medium text-slate-700">機械的な参考情報であり、投資助言ではありません。</span>
      </p>

      <nav className="mb-8 flex flex-wrap gap-2">
        {CATEGORIES.flatMap((c) => c.terms).map((term) => (
          <a
            key={term.id}
            href={`#${term.id}`}
            className="text-xs px-3 py-1 rounded-full bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 transition"
          >
            {term.name}
          </a>
        ))}
      </nav>

      <div className="flex flex-col gap-10">
        {CATEGORIES.map((category) => (
          <section key={category.title}>
            <h2 className="text-lg font-bold text-slate-900 mb-4 pb-2 border-b-2 border-slate-900">{category.title}</h2>
            <div className="flex flex-col gap-4">
              {category.terms.map((term) => (
                <div key={term.id} id={term.id} className="scroll-mt-20 border border-slate-200 rounded-2xl shadow-sm bg-white p-5">
                  <div className="flex items-baseline gap-2 flex-wrap mb-2">
                    <h3 className="font-semibold text-slate-900">{term.name}</h3>
                    <span className="text-xs text-slate-400 font-mono">{term.reading}</span>
                  </div>
                  <div className="text-xs text-slate-500 font-mono bg-slate-50 rounded-lg px-3 py-2 mb-3 inline-block">
                    {term.formula}
                  </div>
                  <p className="text-sm text-slate-600 leading-relaxed mb-2">{term.meaning}</p>
                  <p className="text-sm text-indigo-700 bg-indigo-50 rounded-lg px-3 py-2">{term.rule}</p>
                  {term.caveat && <p className="text-xs text-amber-700 mt-2">⚠ {term.caveat}</p>}
                </div>
              ))}
            </div>
          </section>
        ))}
      </div>
    </>
  );
}
