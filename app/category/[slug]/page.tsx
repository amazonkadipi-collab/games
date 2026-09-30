import type { Metadata } from "next";
import Image from "next/image";
import type { Game } from "@/lib/catalog";


type Props = { params: Promise<{ slug: string }> };

const prettify = (slug: string) => slug.split("-").map(x => x ? x.charAt(0).toUpperCase()+x.slice(1) : x).join(" ");
const matches = (game: Game, slug: string) => {
  const target = slug.replaceAll("-", " ").toLowerCase();
  const values = [game.category, ...game.tags].join(" ").toLowerCase();
  return values.includes(target) || game.category?.toLowerCase().replaceAll(" ","-") === slug;
};

export async function generateMetadata({params}:Props):Promise<Metadata>{
  const {slug}=await params;
  const name=prettify(slug);
  return {title:`${name} Games - Play Online Free`,description:`Play free ${name.toLowerCase()} games online. Browse browser games in the ${name.toLowerCase()} category.`,alternates:{canonical:`/category/${slug}`}};
}

export default async function CategoryPage({params}:Props){
  const {slug}=await params;
  const name=prettify(slug);
  const catalog=await getCatalog();
  const games=catalog.filter(g=>matches(g,slug));
  return <main className="container" style={{padding:"42px 0 70px"}}>
    <a href="/categories" style={{color:"var(--muted)",fontSize:13}}>← All categories</a>
    <h1 style={{fontSize:"clamp(30px,4vw,48px)",margin:"22px 0 10px"}}>{name} Games</h1>
    <p style={{color:"var(--muted)",lineHeight:1.7}}>{games.length ? `Play ${games.length} free ${name.toLowerCase()} games online.` : `Discover free ${name.toLowerCase()} browser games on desktop and mobile.`}</p>
    {games.length ? <div className="grid" style={{marginTop:28}}>{games.map(game=><a className="card" href={`/game/${game.slug}`} key={game.id||game.slug}><div className="thumb">{game.thumbnail?<Image src={game.thumbnail} alt={game.title} width={640} height={360} loading="lazy" />:<span>{game.title}</span>}</div><div className="cardBody"><div className="cardTitle">{game.title}</div><div className="meta">{game.category||name} · Play now</div></div></a>)}</div> : <section className="seoIntro" style={{marginTop:28}}><h2>No live games in this category yet</h2><p>Connect the GameMonetize feed to populate category pages automatically.</p></section>}
  </main>;
}