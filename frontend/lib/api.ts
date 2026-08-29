const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost";

export type User = {
  id: number;
  name: string;
  email: string;
};

function readCookie(name: string): string | null {
  const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));
  return match ? decodeURIComponent(match[1]) : null;
}

async function ensureCsrfCookie(): Promise<void> {
  await fetch(`${API_URL}/sanctum/csrf-cookie`, { credentials: "include" });
}

async function apiFetch(path: string, options: RequestInit = {}): Promise<Response> {
  const xsrfToken = readCookie("XSRF-TOKEN");

  return fetch(`${API_URL}${path}`, {
    ...options,
    credentials: "include",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(xsrfToken ? { "X-XSRF-TOKEN": xsrfToken } : {}),
      ...options.headers,
    },
  });
}

export class LoginError extends Error {}

export async function login(email: string, password: string, remember: boolean): Promise<User> {
  await ensureCsrfCookie();

  const response = await apiFetch("/api/login", {
    method: "POST",
    body: JSON.stringify({ email, password, remember }),
  });

  if (!response.ok) {
    const data = await response.json().catch(() => null);
    throw new LoginError(data?.message ?? "ログインに失敗しました。");
  }

  const data = await response.json();
  return data.user as User;
}

export async function register(
  name: string,
  email: string,
  password: string,
  passwordConfirmation: string
): Promise<User> {
  await ensureCsrfCookie();

  const response = await apiFetch("/api/register", {
    method: "POST",
    body: JSON.stringify({
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    }),
  });

  if (!response.ok) {
    const data = await response.json().catch(() => null);
    const firstError = data?.errors ? Object.values(data.errors)[0] : null;
    const message = Array.isArray(firstError) ? firstError[0] : data?.message;
    throw new LoginError(message ?? "登録に失敗しました。");
  }

  const data = await response.json();
  return data.user as User;
}

export async function logout(): Promise<void> {
  await apiFetch("/api/logout", { method: "POST" });
}

export async function getUser(): Promise<User | null> {
  const response = await apiFetch("/api/user");
  if (!response.ok) return null;

  const data = await response.json();
  return (data.user ?? null) as User | null;
}

export function googleLoginUrl(): string {
  return `${API_URL}/auth/google`;
}

// ---- domain types (mirrors the JSON shape Laravel's Api\* controllers return) ----

export type Market = {
  id: number;
  market: string;
};

export type Theme = {
  id: number;
  user_id: number | null;
  name: string;
  description: string | null;
  color: string | null;
  source: string;
  stocks_count?: number;
};

export type StockScore = {
  code: string;
  overall_score: number | null;
  badge: string | null;
  badge_color: string | null;
  growth_score: number | null;
  growth_label: string | null;
  valuation_score: number | null;
  valuation_label: string | null;
  quality_score: number | null;
  quality_label: string | null;
  current_price: string | null;
  price_date: string | null;
  price_change: string | null;
  price_change_percent: string | null;
  per: string | null;
  pbr: string | null;
  computed_at: string | null;
};

export type Stock = {
  code: string;
  stockName: string;
  market_id: number;
  scale_category: string | null;
  market?: Market | null;
  themes?: Theme[];
  score?: StockScore | null;
};

export type ChartConfig = {
  type: string;
  data: { labels: (string | number)[]; datasets: Record<string, unknown>[] };
  options?: Record<string, unknown>;
};

export type ApiError = { message?: string; errors?: Record<string, string[]> };

async function parseOrThrow<T>(response: Response, fallbackMessage: string): Promise<T> {
  if (!response.ok) {
    const data: ApiError | null = await response.json().catch(() => null);
    const firstError = data?.errors ? Object.values(data.errors)[0] : null;
    const message = (Array.isArray(firstError) ? firstError[0] : undefined) ?? data?.message;
    throw new LoginError(message ?? fallbackMessage);
  }
  if (response.status === 204) return undefined as T;
  return (await response.json()) as T;
}

// ---- dashboard / themes ----

export type DashboardData = {
  themes: Theme[];
  nikkei: string;
  stats: {
    stocks: number;
    scored: number;
    buy_candidates: number;
    last_synced: string | null;
  };
};

export async function getDashboard(): Promise<DashboardData> {
  const response = await apiFetch("/api/dashboard");
  return parseOrThrow(response, "ダッシュボードの取得に失敗しました。");
}

export type ThemeShowData = {
  theme: Theme & { stocks: Stock[] };
  chartConfig: ChartConfig;
  truncated: boolean;
};

export async function getTheme(id: number | string): Promise<ThemeShowData> {
  const response = await apiFetch(`/api/themes/${id}`);
  return parseOrThrow(response, "テーマの取得に失敗しました。");
}

export type ThemeInput = { name: string; description?: string; color?: string };

export async function createTheme(data: ThemeInput): Promise<{ theme: Theme }> {
  const response = await apiFetch("/api/themes", { method: "POST", body: JSON.stringify(data) });
  return parseOrThrow(response, "テーマの作成に失敗しました。");
}

export async function updateTheme(id: number | string, data: ThemeInput): Promise<{ theme: Theme }> {
  const response = await apiFetch(`/api/themes/${id}`, { method: "PUT", body: JSON.stringify(data) });
  return parseOrThrow(response, "テーマの更新に失敗しました。");
}

export async function deleteTheme(id: number | string): Promise<void> {
  const response = await apiFetch(`/api/themes/${id}`, { method: "DELETE" });
  return parseOrThrow(response, "テーマの削除に失敗しました。");
}

// ---- stocks ----

export type Paginated<T> = {
  current_page: number;
  data: T[];
  last_page: number;
  total: number;
  per_page: number;
  from: number | null;
  to: number | null;
};

export type StocksIndexData = {
  stocks: Paginated<Stock>;
  themes: Theme[];
  badges: string[];
  favoriteCodes: string[];
};

export async function getStocks(params: Record<string, string> = {}): Promise<StocksIndexData> {
  const qs = new URLSearchParams(params).toString();
  const response = await apiFetch(`/api/stocks${qs ? `?${qs}` : ""}`);
  return parseOrThrow(response, "銘柄一覧の取得に失敗しました。");
}

export type TrendRow = {
  fiscal_year: number;
  net_sales: number | null;
  operating_profit: number | null;
  ordinary_profit: number | null;
  profit: number | null;
  eps: number | null;
  roe: number | null;
  roa: number | null;
  equity_ratio: number | null;
  dividend_per_share: number | null;
  payout_ratio: number | null;
  yoy_net_sales: number | null;
  yoy_operating_profit: number | null;
  yoy_profit: number | null;
  operating_margin: number | null;
  ordinary_margin: number | null;
  net_margin: number | null;
};

export type StockShowData = {
  stock: Stock;
  trendRows: TrendRow[];
  cagr: { net_sales: number | null; operating_profit: number | null; profit: number | null };
  cagrYears: number;
  salesChartConfig: ChartConfig;
  profitChartConfig: ChartConfig;
  ratioChartConfig: ChartConfig;
  syncError: string | null;
  scoreRecord: StockScore;
  overview: string;
  isFavorited: boolean;
};

export async function getStock(code: string): Promise<StockShowData> {
  const response = await apiFetch(`/api/stocks/${code}`);
  return parseOrThrow(response, "銘柄詳細の取得に失敗しました。");
}

// ---- favorites ----

export async function getFavorites(): Promise<{ stocks: Stock[] }> {
  const response = await apiFetch("/api/favorites");
  return parseOrThrow(response, "お気に入りの取得に失敗しました。");
}

export async function addFavorite(code: string): Promise<void> {
  const response = await apiFetch("/api/favorites", { method: "POST", body: JSON.stringify({ code }) });
  return parseOrThrow(response, "お気に入りの追加に失敗しました。");
}

export async function removeFavorite(code: string): Promise<void> {
  const response = await apiFetch(`/api/favorites/${code}`, { method: "DELETE" });
  return parseOrThrow(response, "お気に入りの解除に失敗しました。");
}

export async function getStockThemes(code: string): Promise<{ stock: Stock; themes: Theme[] }> {
  const response = await apiFetch(`/api/stocks/${code}/themes`);
  return parseOrThrow(response, "テーマ編集情報の取得に失敗しました。");
}

export async function updateStockThemes(code: string, themeIds: number[]): Promise<{ stock: Stock }> {
  const response = await apiFetch(`/api/stocks/${code}/themes`, {
    method: "PUT",
    body: JSON.stringify({ theme_ids: themeIds }),
  });
  return parseOrThrow(response, "テーマの保存に失敗しました。");
}

// ---- SBI holdings ----

export type SbiHolding = {
  id: number;
  user_id: number;
  code: string;
  shares: number;
  average_acquisition_price: string;
  acquisition_date: string | null;
  account_type: "specific" | "general" | "nisa" | null;
  memo: string | null;
  acquisition_cost: number;
  stock?: Stock;
};

export type SbiHoldingRow = {
  holding: SbiHolding;
  current_price: number | null;
  price_date: string | null;
  price_change: number | null;
  price_change_percent: number | null;
  computed_at: string | null;
  market_value: number | null;
  unrealized_pl: number | null;
};

export type PortfolioShare = { name: string; share: number };

export type PortfolioAnalysis = {
  total_value: number;
  by_stock: PortfolioShare[];
  by_theme: PortfolioShare[];
  suggestions: string[];
};

export type SbiHoldingsData = {
  holdings: SbiHoldingRow[];
  allocationChartConfig: ChartConfig;
  summary: { cost: number; value: number | null; pl: number | null };
  portfolio: PortfolioAnalysis;
};

export async function getSbiHoldings(): Promise<SbiHoldingsData> {
  const response = await apiFetch("/api/sbi-holdings");
  return parseOrThrow(response, "SBI保有銘柄の取得に失敗しました。");
}

export async function getSbiHoldingStocks(): Promise<{ stocks: Pick<Stock, "code" | "stockName">[] }> {
  const response = await apiFetch("/api/sbi-holdings/stocks");
  return parseOrThrow(response, "銘柄候補の取得に失敗しました。");
}

export async function getSbiHolding(id: number | string): Promise<{ holding: SbiHolding }> {
  const response = await apiFetch(`/api/sbi-holdings/${id}`);
  return parseOrThrow(response, "保有銘柄の取得に失敗しました。");
}

export type SbiHoldingInput = {
  code: string;
  shares: number;
  average_acquisition_price: number;
  acquisition_date?: string;
  account_type?: "specific" | "general" | "nisa" | "";
  memo?: string;
};

export async function createSbiHolding(data: SbiHoldingInput): Promise<{ holding: SbiHolding }> {
  const response = await apiFetch("/api/sbi-holdings", { method: "POST", body: JSON.stringify(data) });
  return parseOrThrow(response, "保有銘柄の登録に失敗しました。");
}

export async function updateSbiHolding(id: number, data: SbiHoldingInput): Promise<{ holding: SbiHolding }> {
  const response = await apiFetch(`/api/sbi-holdings/${id}`, { method: "PUT", body: JSON.stringify(data) });
  return parseOrThrow(response, "保有銘柄の更新に失敗しました。");
}

export async function deleteSbiHolding(id: number): Promise<void> {
  const response = await apiFetch(`/api/sbi-holdings/${id}`, { method: "DELETE" });
  return parseOrThrow(response, "保有銘柄の削除に失敗しました。");
}
