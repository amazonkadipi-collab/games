import type { Metadata } from "next";
type Props={params:Promise<{slug:string}>};
export async function generateMetadata({params}:Props):Promise<Metadata>{
 const {slug}=await params; const name=slug.split("-").map(x=>x.charAt(0).toUpperCase()+x.slice(1)).join(" ");
 return {title:`${name} Games - Play Online Free`,description:`Play free ${name.toLowerCase()} games online. Browse popular and new browser games in this category.`,alternates:{canonical:`/category/${slug}`}};
}
export default async function CategoryPage({params}:Props){
 const {slug}=await params; const name=slug.split("-").map(x=>x.charAt(0).toUpperCase()+x.slice(1)).join(" ");
 const games=["Action Arena","Drift Legends","Block Puzzle","Stickman Clash","Zombie Escape","Monster Run","Ninja Jump","Moto Rush"];
 return <main className="container" style={{padding:"42px 0 70px"}}>
  <a href="/categories" style={{color:"var(--muted)",fontSize:13}}>← All categories</a>
  <h1 style={{fontSize:"clamp(30px,4vw,48px)",margin:"22px 0 10px"}}>{name} Games</h1>
  <p style={{color:"var(--muted)",lineHeight:1.7}}>Play free {name.toLowerCase()} games online. Discover browser games you can start instantly on desktop and mobile.</p>
  <div className="grid" style={{marginTop:28}}>{games.map(title=><a className="card" href={"/game/"+title.toLowerCase().replaceAll(" ","-")} key={title}><div className="thumb">{title}</div><div className="cardBody"><div className="cardTitle">{title}</div><div className="meta">{name} · Play now</div></div></a>)}</div>
 </main>;
}