export type Game = {
  id: string;
  slug: string;
  title: string;
  description: string;
  thumbnail: string;
  url: string;
  category: string;
  tags: string[];
  mobileReady: boolean;
  publishedAt?: string;
};

function clean(value: unknown) {
  return typeof value === "string" ? value.trim() : "";
}

function first(...values: unknown[]) {
  for (const value of values) {
    const v = clean(value);
    if (v) return v;
  }
  return "";
}

function slugify(value: string) {
  return value.toLowerCase().normalize("NFKD").replace(/[^\w\s-]/g, "").replace(/\s+/g, "-").replace(/-+/g, "-").replace(/^-|-$/g, "");
}

function textValue(value: unknown): string {
  if (typeof value === "string") return value;
  if (Array.isArray(value)) return value.map(textValue).filter(Boolean).join(", ");
  if (value && typeof value === "object") {
    const record = value as Record<string, unknown>;
    return first(record.name, record.title, record.value, record["#text"]);
  }
  return "";
}

function categoriesOf(raw: Record<string, unknown>) {
  const value = raw.category ?? raw.categories ?? raw.genre;
  if (Array.isArray(value)) return value.map(textValue).filter(Boolean);
  const one = textValue(value);
  return one ? one.split(/[,|]/).map(x => x.trim()).filter(Boolean) : [];
}

function tagsOf(raw: Record<string, unknown>) {
  const value = raw.tags ?? raw.tag;
  if (Array.isArray(value)) return value.map(textValue).filter(Boolean);
  const one = textValue(value);
  return one ? one.split(/[,|]/).map(x => x.trim()).filter(Boolean) : [];
}

export function normalizeGames(input: unknown): Game[] {
  const root = input as Record<string, unknown>;
  const list = Array.isArray(input)
    ? input
    : Array.isArray(root.games) ? root.games
    : Array.isArray(root.items) ? root.items
    : Array.isArray(root.game) ? root.game
    : [];

  return list.map((item, index) => {
    const raw = (item && typeof item === "object" ? item : {}) as Record<string, unknown>;
    const title = first(raw.title, raw.name, raw.gameName) || "Untitled Game";
    const id = first(raw.id, raw.gameId, raw.game_id) || String(index);
    const categories = categoriesOf(raw);
    return {
      id,
      slug: slugify(title) + "-" + id,
      title,
      description: first(raw.description, raw.desc),
      thumbnail: first(raw.thumbnail, raw.thumb, raw.image, raw.thumbnailUrl),
      url: first(raw.url, raw.gameUrl, raw.game_url, raw.link, raw.embed),
      category: categories[0] || "Games",
      tags: tagsOf(raw),
      mobileReady: String(raw.mobileReady ?? raw.mobile_ready ?? "").toLowerCase() === "yes",
      publishedAt: first(raw.published, raw.publishedAt, raw.date) || undefined
    };
  }).filter(game => game.title && (game.url || game.thumbnail));
}

export async function getCatalog(): Promise<Game[]> {
  const feedUrl = process.env.GAME_MONETIZE_FEED_URL;
  if (!feedUrl) return [];
  const response = await fetch(feedUrl, { next: { revalidate: 900 } });
  if (!response.ok) throw new Error("Game catalog feed returned " + response.status);
  const contentType = response.headers.get("content-type") || "";
  if (contentType.includes("json") || feedUrl.toLowerCase().includes(".json")) {
    return normalizeGames(await response.json());
  }
  const text = await response.text();
  try {
    return normalizeGames(JSON.parse(text));
  } catch {
    return [];
  }
}