import type { Metadata } from "next";
import Image from "next/image";
import { getCatalog } from "@/lib/catalog";
import { notFound } from "next/navigation";

type Props = { params: Promise<{ slug: string }> };

export async function generateMetadata({params}: Props): Promise<Metadata> {
  const {slug}=await params;
  const games=await getCatalog().catch(()=>[]);
  const game=games.find(item=>item.slug===slug);
  if (!game) return { title:"Game Not Found", robots:{index:false,follow:false} };
  return {
    title: game.title + " - Play Online Free",
    description: game.description || "Play " + game.title + " online in your browser.",
    robots:{index:true,follow:true},
    alternates:{canonical:"/game/"+slug},
    openGraph:{title:game.title+" - Play Online Free",description:game.description || "Play online for free.",images:game.thumbnail?[{url:game.thumbnail}]:[]}
  };
}

export default async function GamePage({params}: Props) {
  const {slug}=await params;
  const games=await getCatalog().catch(()=>[]);
  const game=games.find(item=>item.slug===slug);
  if (!game) notFound();
  const similar=games.filter(item=>item.id!==game.id && (item.category===game.category || item.tags.some(tag=>game.tags.includes(tag)))).slice(0,6);

  return <main className="container gamePage">
    <nav className="breadcrumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>›</span><a href={"/category/"+game.category.toLowerCase().replaceAll(" ","-")}>{game.category}</a><span>›</span><span>{game.title}</span></nav>
    <div className="gameHeader"><div><div className="kicker">BROWSER GAME</div><h1>{game.title}</h1><p>Play {game.title} online for free.</p></div></div>
    <div className="playerBox">
      {game.url ? <iframe src={game.url} title={game.title} allow="fullscreen; autoplay; gamepad" allowFullScreen /> : <div className="playerMessage"><strong>Game unavailable</strong><span>The game provider did not return a playable URL.</span></div>}
    </div>
    <div className="gameColumns">
      <section className="gameContent">
        <h2>About {game.title}</h2><p>{game.description || "Play this browser game online and discover its gameplay."}</p>
        <h2>How to Play</h2><p>Use the controls provided inside the game. The game is designed for browser play and may support desktop and mobile devices.</p>
        {game.tags.length>0 && <><h2>Tags</h2><div className="tags">{game.tags.map(tag=><a href={"/search?q="+encodeURIComponent(tag)} key={tag}>#{tag}</a>)}</div></>}
      </section>
      <aside className="gameInfo"><div><span>Category</span><a href={"/category/"+game.category.toLowerCase().replaceAll(" ","-")}>{game.category}</a></div><div><span>Platform</span><b>Web browser</b></div><div><span>Mobile ready</span><b>{game.mobileReady?"Yes":"Check game"}</b></div><div><span>Provider</span><b>GameMonetize</b></div></aside>
    </div>
    {similar.length>0 && <section className="section"><div className="sectionHead"><div><div className="kicker">KEEP PLAYING</div><h2>More Games</h2></div><a href="/games">View all →</a></div><div className="grid">{similar.map(item=><a className="card" href={"/game/"+item.slug} key={item.id}><div className="thumb">{item.thumbnail ? <Image src={item.thumbnail} alt={item.title} width={640} height={360} loading="lazy"/> : item.title}</div><div className="cardBody"><div className="cardTitle">{item.title}</div><div className="meta">{item.category} · Play now</div></div></a>)}</div></section>}
  </main>;
}