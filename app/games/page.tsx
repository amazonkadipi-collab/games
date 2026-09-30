import Image from "next/image";
import { getCatalog } from "@/lib/catalog";
export const metadata={title:"All Free Online Games",description:"Browse free online browser games by genre and discover new games to play instantly."};

export default async function GamesPage(){
  const games=await getCatalog().catch(()=>[]);
  return <main className="container" style={{padding:"42px 0 70px"}}>
    <a href="/" style={{color:"var(--muted)",fontSize:13}}>← Home</a>
    <h1 style={{fontSize:"clamp(30px,4vw,48px)",margin:"22px 0 10px"}}>All Free Online Games</h1>
    <p style={{color:"var(--muted)",lineHeight:1.7}}>Browse the live game catalog and play browser games instantly.</p>
    <div className="grid" style={{marginTop:28}}>
      {games.map(game=><a className="card" href={"/game/"+game.slug} key={game.id}>
        <div className="thumb">{game.thumbnail ? <Image src={game.thumbnail} alt={game.title} width={640} height={360} loading="lazy"/> : game.title}</div>
        <div className="cardBody"><div className="cardTitle">{game.title}</div><div className="meta">{game.category} · Play online</div></div>
      </a>)}
    </div>
    {games.length===0&&<div className="emptyState">Add GAME_MONETIZE_FEED_URL in Vercel to load the live catalog.</div>}
  </main>
}