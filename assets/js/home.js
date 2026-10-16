/**
 * Forma 3D — homepage 3D stage.
 *
 * One fixed WebGL canvas renders every homepage model. Each model sits in a
 * "holder" whose state object (x, y, rot, scale…) is derived every frame from
 * the scroll position through simple keyframe tracks; the render loop turns
 * that state into transforms. x/y are fractions of the visible viewport,
 * scale is a fraction of its height.
 */
import * as THREE from 'three';
import { GLTFLoader } from '../vendor/three/addons/loaders/GLTFLoader.js';
import { MeshoptDecoder } from '../vendor/three/addons/libs/meshopt_decoder.module.js';
import { RoomEnvironment } from '../vendor/three/addons/environments/RoomEnvironment.js';

const E = window.forma3d;
const DATA = window.forma3dHome || {};
const $ = (s, c = document) => c.querySelector(s);
const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));
const mobile = () => window.innerWidth < 960;
const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const TAU = Math.PI * 2;

const canvas = document.getElementById('stage-canvas');
const loaderEl = $('[data-loader]');

/* ------------------------------------------------------------------------
 * Renderer, scene, camera
 * ---------------------------------------------------------------------- */
let renderer;
try {
	renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: 'high-performance' });
} catch (e) {
	renderer = null;
}

const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(30, window.innerWidth / window.innerHeight, 0.1, 100);
const target = new THREE.Vector3(0, 0, 0);
camera.position.set(0, 0.9, 8);
camera.lookAt(target);
const view = { w: 1, h: 1 };

if (renderer) {
	renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
	renderer.outputColorSpace = THREE.SRGBColorSpace;
	renderer.toneMapping = THREE.NeutralToneMapping;
	renderer.toneMappingExposure = 1.0;
	const pmrem = new THREE.PMREMGenerator(renderer);
	scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
	scene.environmentIntensity = 0.95;
	const key = new THREE.DirectionalLight(0xfff4e8, 1.4);
	key.position.set(3, 6, 5);
	scene.add(key);
	const rim = new THREE.DirectionalLight(0xe8eeff, 0.6);
	rim.position.set(-5, 3, -4);
	scene.add(rim);
}

function resize() {
	const w = window.innerWidth;
	const h = window.innerHeight;
	camera.aspect = w / h;
	camera.updateProjectionMatrix();
	const d = camera.position.distanceTo(target);
	view.h = 2 * d * Math.tan(THREE.MathUtils.degToRad(camera.fov / 2));
	view.w = view.h * camera.aspect;
	if (renderer) renderer.setSize(w, h, false);
}
resize();
window.addEventListener('resize', resize);

/* Soft contact shadow shared by every holder. */
const shadowTex = (() => {
	const c = document.createElement('canvas');
	c.width = c.height = 256;
	const g = c.getContext('2d');
	const grd = g.createRadialGradient(128, 128, 0, 128, 128, 128);
	grd.addColorStop(0, 'rgba(30,22,14,0.55)');
	grd.addColorStop(0.45, 'rgba(30,22,14,0.22)');
	grd.addColorStop(1, 'rgba(30,22,14,0)');
	g.fillStyle = grd;
	g.fillRect(0, 0, 256, 256);
	const t = new THREE.CanvasTexture(c);
	t.colorSpace = THREE.SRGBColorSpace;
	return t;
})();

/* ------------------------------------------------------------------------
 * Holders
 * ---------------------------------------------------------------------- */
const holders = [];

function createHolder(name) {
	const holder = new THREE.Group();
	const spin = new THREE.Group();
	holder.add(spin);
	scene.add(holder);
	const h = {
		name,
		holder,
		spin,
		fit: null,
		norm: 1,
		halfH: 0,
		size: new THREE.Vector3(),
		variants: null,
		// animated state
		st: { x: 0, y: 0, rot: 0, tilt: 0, scale: 0, intro: 1, spinIntro: 0 },
		float: Math.random() * TAU,
	};
	holders.push(h);
	return h;
}

function mountModel(h, gltf) {
	const model = gltf.scene;
	const box = new THREE.Box3().setFromObject(model);
	const size = box.getSize(new THREE.Vector3());
	const center = box.getCenter(new THREE.Vector3());
	model.position.set(-center.x, -box.min.y - size.y / 2, -center.z); // pivot at visual centre
	model.traverse((o) => {
		if (o.isLight) o.visible = false;
		if (o.isMesh && o.material) o.material.envMapIntensity = 1;
	});
	const fit = new THREE.Group();
	fit.add(model);
	h.spin.add(fit);
	h.fit = fit;
	h.size.copy(size);
	h.halfH = size.y / 2;
	// "Visual size": wide pieces count less than tall ones so sofas and chairs read at similar weight.
	h.norm = 1 / Math.max(size.y, Math.max(size.x, size.z) * 0.68);

	const footprint = Math.max(size.x, size.z);
	const shadow = new THREE.Mesh(
		new THREE.PlaneGeometry(1, 1),
		new THREE.MeshBasicMaterial({ map: shadowTex, transparent: true, depthWrite: false, toneMapped: false })
	);
	shadow.rotation.x = -Math.PI / 2;
	shadow.scale.set(size.x * 1.35, size.z * 1.35, 1);
	shadow.position.y = -size.y / 2 + 0.002;
	shadow.renderOrder = -1;
	fit.add(shadow);
	h.shadow = shadow;

	h.variants = setupVariants(gltf);
	h.materials = [];
	model.traverse((o) => {
		if (o.isMesh) {
			(Array.isArray(o.material) ? o.material : [o.material]).forEach((m) => {
				if (!h.materials.includes(m)) {
					m.userData.baseColor = m.color.clone();
					h.materials.push(m);
				}
			});
		}
	});
}

/* KHR_materials_variants support (the GLTFLoader does not apply variants itself). */
function setupVariants(gltf) {
	const ext = gltf.parser.json.extensions && gltf.parser.json.extensions.KHR_materials_variants;
	if (!ext) return null;
	const names = ext.variants.map((v) => v.name);
	const entries = [];
	gltf.scene.traverse((o) => {
		if (!o.isMesh) return;
		const assoc = gltf.parser.associations.get(o);
		if (!assoc || assoc.meshes === undefined) return;
		const prim = gltf.parser.json.meshes[assoc.meshes].primitives[assoc.primitives];
		const mappings = prim.extensions && prim.extensions.KHR_materials_variants && prim.extensions.KHR_materials_variants.mappings;
		if (mappings) entries.push({ mesh: o, original: o.material, mappings });
	});
	return {
		names,
		async select(name) {
			const vi = names.indexOf(name);
			await Promise.all(entries.map(async (e) => {
				const m = e.mappings.find((mp) => mp.variants.includes(vi));
				e.mesh.material = m ? await gltf.parser.getDependency('material', m.material) : e.original;
			}));
		},
	};
}

function applyFinish(h, f) {
	if (!h || !f) return;
	if (f.variant && h.variants) {
		h.variants.select(f.variant);
		return;
	}
	const tint = f.tint || [1, 1, 1];
	(h.materials || []).forEach((m) => {
		if (f.material && m.name !== f.material) return;
		m.color.copy(m.userData.baseColor).multiply(new THREE.Color(tint[0], tint[1], tint[2]));
	});
}

/* ------------------------------------------------------------------------
 * Load everything
 * ---------------------------------------------------------------------- */
const heroH = DATA.hero ? createHolder('hero') : null;
const scH = (DATA.showcase || []).map((p) => createHolder(p.name));
const anH = DATA.anatomy ? createHolder('anatomy') : null;

const jobs = [];
if (heroH) jobs.push([heroH, DATA.hero]);
scH.forEach((h, i) => jobs.push([h, DATA.showcase[i]]));
if (anH) jobs.push([anH, DATA.anatomy]);

const countEl = $('[data-loader-count]');
const barEl = $('[data-loader-bar]');
const shown = { v: 0 };
const progress = new Array(jobs.length).fill(0);

function setLoad() {
	const p = jobs.length ? progress.reduce((a, b) => a + b, 0) / jobs.length : 1;
	E.tween(shown, { v: p * 100 }, {
		duration: 0.6,
		ease: 'power2Out',
		onUpdate: () => {
			if (countEl) countEl.textContent = Math.round(shown.v);
			if (barEl) barEl.style.transform = `scaleX(${shown.v / 100})`;
		},
	});
}

async function loadAll() {
	if (!renderer || !jobs.length) return;
	const loader = new GLTFLoader().setMeshoptDecoder(MeshoptDecoder);
	await Promise.all(
		jobs.map(([h, d], i) =>
			loader
				.loadAsync(d.model, (e) => {
					if (e.total) progress[i] = e.loaded / e.total;
					setLoad();
				})
				.then((gltf) => {
					mountModel(h, gltf);
					if (d.finishes && d.finishes[0]) applyFinish(h, d.finishes[0]);
					progress[i] = 1;
					setLoad();
				})
				.catch(() => {
					progress[i] = 1;
					setLoad();
				})
		)
	);
	// Upload textures/shaders before the curtain lifts so the first scroll frames don't hitch.
	renderer.compile(scene, camera);
}

/* ------------------------------------------------------------------------
 * Keyframe helpers
 * ---------------------------------------------------------------------- */
const { clamp, lerp, ease } = E;
const PROPS = ['x', 'y', 'rot', 'scale', 'tilt'];

/**
 * Sample a keyframe track at time t. A track is a start state plus segments
 * { at, dur, to: {…}, ease }; each segment animates from whatever the state is
 * when it starts, like a timeline.
 */
function sampleTrack(start, segments, t) {
	const s = { ...start };
	for (const seg of segments) {
		if (t <= seg.at) break;
		const from = { ...s };
		const k = (seg.ease || ease.linear)(clamp((t - seg.at) / seg.dur));
		Object.keys(seg.to).forEach((key) => { s[key] = lerp(from[key], seg.to[key], k); });
	}
	return s;
}

/** Exponential smoothing toward a moving target (the "scrub" feel). */
function smoother(tau) {
	let v = null;
	return (target, dt) => {
		v = v === null ? target : v + (target - v) * (1 - Math.exp(-dt / tau));
		return v;
	};
}

const pick = (desk, mob) => (mobile() ? mob : desk);
const vh = () => window.innerHeight;
const top = (el) => el.getBoundingClientRect().top + window.scrollY;
const assign = (st, v) => PROPS.forEach((k) => { if (k in v) st[k] = v[k]; });

/* ------------------------------------------------------------------------
 * Sections. Each returns an update(dt) function called every frame.
 * ---------------------------------------------------------------------- */
function heroSection() {
	const hero = $('[data-hero]');
	if (!hero) return null;
	const statement = $('[data-statement]');
	const title = $('.hero__title', hero);
	const fades = $$('.hero__meta, .hero__foot', hero);
	const smooth = smoother(0.28);
	return (dt) => {
		const hTop = top(hero);
		const hH = hero.offsetHeight;
		const out = E.progress(hTop, hTop + hH);
		if (title) title.style.transform = `translate3d(0, ${(-18 * out).toFixed(2)}%, 0)`;
		if (title) title.style.opacity = (1 - 0.85 * out).toFixed(3);
		const fade = E.progress(hTop + hH * 0.1, hTop + hH * 0.6);
		fades.forEach((el) => { el.style.opacity = (1 - fade).toFixed(3); });
		if (!heroH) return;
		const end = statement ? top(statement) + statement.offsetHeight - vh() * 0.65 : hTop + hH;
		const t = smooth(E.progress(hTop, end), dt) * 2.45;
		assign(heroH.st, sampleTrack(
			{ x: pick(0.25, 0.04), y: pick(-0.03, -0.13), rot: 0.55, scale: pick(0.5, 0.34), tilt: 0.04 },
			[
				{ at: 0, dur: 1, to: { x: pick(-0.24, 0), y: pick(0, 0.2), rot: 0.55 + Math.PI, scale: pick(0.58, 0.36), tilt: 0.1 } },
				{ at: 1, dur: 1, to: { rot: 0.55 + Math.PI * 1.7 } },
				{ at: 2, dur: 0.45, to: { rot: 0.55 + Math.PI * 2.1, scale: 0, y: pick(0.25, 0.4) } },
			],
			t
		));
	};
}

function showcaseSection() {
	const sec = $('[data-showcase]');
	if (!sec || !scH.length) return null;
	const n = scH.length;
	const items = $$('[data-sc-item]', sec);
	const navs = $$('[data-sc-nav]', sec);
	const idxEl = $('[data-sc-index]', sec);
	const big = $('[data-sc-bignum]', sec);
	const pad = (i) => String(i + 1).padStart(2, '0');
	const IN = 0.35;
	const D = n + 0.15;
	const smooth = smoother(0.22);
	const smoothLeave = smoother(0.25);
	const tiltBoost = scH.map(() => ({ v: 0 }));
	let active = -1;
	let inRange = false;

	const range = () => {
		const sTop = top(sec);
		return [sTop - vh() * 0.55, sTop + sec.offsetHeight - vh(), sTop + sec.offsetHeight];
	};

	function setActive(t) {
		const i = Math.max(0, Math.min(n - 1, Math.floor(t + 0.02)));
		navs.forEach((nv, k) => nv.style.setProperty('--p', clamp(t - k).toFixed(3)));
		if (i === active) return;
		active = i;
		items.forEach((it, k) => it.classList.toggle('is-active', k === i));
		navs.forEach((nv, k) => nv.classList.toggle('is-active', k === i));
		if (idxEl) idxEl.textContent = pad(i);
		if (big) {
			big.textContent = pad(i);
			const o = { v: 0 };
			E.tween(o, { v: 1 }, { duration: 0.9, onUpdate: () => { big.style.opacity = o.v.toFixed(3); } });
		}
		if (inRange) document.body.style.backgroundColor = (DATA.showcase[i] && DATA.showcase[i].bg) || '';
	}

	navs.forEach((nv, k) => nv.addEventListener('click', () => {
		const [start, end] = range();
		E.scrollTo(start + ((k + 0.45) / D) * (end - start), 1.4);
	}));

	$$('[data-sc-finish]', sec).forEach((b) => b.addEventListener('click', () => {
		const [i, fi] = b.dataset.scFinish.split(':').map(Number);
		applyFinish(scH[i], DATA.showcase[i].finishes[fi]);
		$$('[data-sc-finish^="' + i + ':"]', sec).forEach((x) => x.classList.toggle('is-active', x === b));
		tiltBoost[i].v = 0.19;
		E.tween(tiltBoost[i], { v: 0 }, { duration: 1.2, ease: 'elasticOut' });
	}));

	return (dt) => {
		const [start, end, bottom] = range();
		const y = window.scrollY;
		const nowIn = y >= start && y <= end;
		if (nowIn !== inRange) {
			inRange = nowIn;
			document.body.style.backgroundColor = inRange && active >= 0 ? ((DATA.showcase[active] && DATA.showcase[active].bg) || '') : '';
		}
		const t = smooth(E.progress(start, end), dt) * D;
		const leave = smoothLeave(E.progress(bottom - vh(), bottom - vh() * 0.3), dt);
		const X = pick(0.19, 0);
		const Y = pick(-0.02, 0.14);
		const S = pick(0.52, 0.42);
		scH.forEach((h, i) => {
			const last = i === n - 1;
			const t0 = i === 0 ? 0 : i - 0.05;
			const segs = [
				{ at: t0, dur: IN, to: { x: X, y: Y, rot: -0.35, scale: S }, ease: ease.power2Out },
				{ at: t0 + IN, dur: (last ? n + 0.1 - t0 : 1) - IN - 0.05, to: { rot: last ? 1.2 : 0.9 } },
			];
			if (!last) segs.push({ at: i + 0.72, dur: 0.28, to: { scale: 0, rot: 1.9, x: X - 0.12 }, ease: ease.power2In });
			const st = sampleTrack({ x: X + 0.12, y: Y, rot: -1.4, scale: 0, tilt: 0.06 }, segs, t);
			if (last && leave > 0) {
				const k = ease.power1In(leave);
				st.scale = lerp(st.scale, 0, k);
				st.y = lerp(st.y, Y + 0.2, k);
			}
			st.tilt += tiltBoost[i].v;
			assign(h.st, st);
		});
		setActive(t);
	};
}

function roomsSection() {
	const sec = $('[data-hscroll]');
	const track = $('[data-hscroll-track]');
	if (!sec || !track) return null;
	const bar = $('[data-hscroll-progress]', sec);
	const intro = $('.rooms__intro', sec);
	const imgs = $$('[data-hparallax]', sec);
	const smooth = smoother(0.25);
	let distance = 0;
	const measure = () => {
		distance = Math.max(0, track.scrollWidth + track.offsetLeft - window.innerWidth);
		sec.style.height = `${window.innerHeight + distance}px`;
	};
	measure();
	window.addEventListener('resize', measure);
	window.addEventListener('load', measure);
	return (dt) => {
		const sTop = top(sec);
		const p = smooth(E.progress(sTop, sTop + distance), dt);
		track.style.transform = `translate3d(${(-distance * p).toFixed(1)}px, 0, 0)`;
		if (bar) bar.style.transform = `scaleX(${p.toFixed(4)})`;
		if (intro && !mobile()) {
			const f = E.progress(sTop, sTop + window.innerWidth * 0.35);
			intro.style.opacity = (1 - f).toFixed(3);
			intro.style.transform = `translate3d(${(-60 * f).toFixed(1)}px, 0, 0)`;
		}
		const vw = window.innerWidth;
		imgs.forEach((img) => {
			const r = img.closest('.room-card').getBoundingClientRect();
			const q = clamp((vw - r.left) / (vw + r.width));
			img.style.transform = `translate3d(${lerp(8, -8, q).toFixed(2)}%, 0, 0)`;
		});
	};
}

function anatomySection() {
	const sec = $('[data-anatomy]');
	if (!sec || !anH) return null;
	const smoothIn = smoother(0.28);
	const smoothPin = smoother(0.28);
	const smoothOut = smoother(0.28);
	const SHOW = [[0.05, 0.95], [0.95, 1.9], [1.9, 2.95]];
	return (dt) => {
		const sTop = top(sec);
		const sBottom = sTop + sec.offsetHeight;
		const X = 0;
		const Y = pick(-0.06, 0.02);
		const S = pick(0.5, 0.42);
		const pin = E.progress(sTop, sBottom - vh());
		anatomyState.active = window.scrollY >= sTop && window.scrollY <= sBottom - vh();
		const enter = ease.power1Out(smoothIn(E.progress(sTop - vh() * 0.75, sTop), dt));
		let st = {
			x: X, y: lerp(Y - 0.3, Y, enter), rot: lerp(-1.2, 0.35, enter), scale: lerp(0, S, enter), tilt: 0.1,
		};
		const t = smoothPin(pin, dt) * 3;
		if (t > 0) {
			st = sampleTrack(st, [
				{ at: 0, dur: 1, to: { rot: 0.35 + Math.PI * 0.55, scale: S * 1.12, tilt: 0.16 } },
				{ at: 1, dur: 1, to: { rot: 0.35 + Math.PI * 1.25, scale: S, tilt: 0.06 } },
				{ at: 2, dur: 1, to: { rot: 0.35 + TAU } },
			], t);
		}
		const out = smoothOut(E.progress(sBottom - vh(), sBottom - vh() * 0.35), dt);
		if (out > 0) {
			const k = ease.power1In(out);
			st.scale = lerp(st.scale, 0, k);
			st.y = lerp(st.y, Y + 0.25, k);
		}
		assign(anH.st, st);
		SHOW.forEach(([a, b], i) => {
			anatomyState.show['a' + i] = Math.min(clamp((t - a) / 0.18), clamp((b - t) / 0.18));
		});
		if (!anatomyState.active) callouts.forEach((c) => { c.style.opacity = 0; });
	};
}

/* ------------------------------------------------------------------------
 * Render loop
 * ---------------------------------------------------------------------- */
const mouse = { x: 0, y: 0, tx: 0, ty: 0 };
window.addEventListener('pointermove', (e) => {
	mouse.tx = (e.clientX / window.innerWidth) * 2 - 1;
	mouse.ty = (e.clientY / window.innerHeight) * 2 - 1;
}, { passive: true });

const callouts = $$('[data-callout]');
const anatomyAnchors = [
	// Fractions of the model's half-extents (x, z) and height (y, 0 = floor, 1 = top).
	[0.1, 0.62, 0.45],
	[-0.25, 0.92, -0.55],
	[0.82, 0.08, 0.72],
];
const tmpV = new THREE.Vector3();
const anatomyState = { active: false, show: { a0: 0, a1: 0, a2: 0 } };

function render(time, dt) {
	if (!renderer) return;
	const k = 1 - Math.exp(-dt / 0.33);
	mouse.x += (mouse.tx - mouse.x) * k;
	mouse.y += (mouse.ty - mouse.y) * k;

	let any = false;
	for (const h of holders) {
		const s = h.st;
		let scale = Math.max(0, s.scale * s.intro) * view.h * h.norm;
		// Keep wide pieces inside narrow (portrait) viewports.
		scale = Math.min(scale, (view.w * 0.88 * Math.max(0, s.scale * s.intro)) / (0.55 * Math.max(h.size.x, h.size.z) || 1));
		h.holder.visible = !!h.fit && scale > 0.001;
		if (!h.holder.visible) continue;
		any = true;
		const bob = reduced ? 0 : Math.sin(time * 0.9 + h.float) * 0.012 * view.h;
		h.holder.position.set(s.x * view.w + mouse.x * 0.06, s.y * view.h + bob - mouse.y * 0.03, 0);
		h.holder.scale.setScalar(scale);
		h.spin.rotation.y = s.rot + s.spinIntro + mouse.x * 0.18;
		h.spin.rotation.x = s.tilt + mouse.y * 0.04;
	}

	if (anH && anH.fit && anatomyState.active) placeCallouts();

	if (any) renderer.render(scene, camera);
	else renderer.clear();
}

function placeCallouts() {
	const w = window.innerWidth;
	const hgt = window.innerHeight;
	const s = anH.size;
	anH.fit.updateWorldMatrix(true, false);
	callouts.forEach((el, i) => {
		const a = anatomyAnchors[i];
		if (!a) return;
		tmpV.set(a[0] * s.x * 0.5, a[1] * s.y - s.y / 2, a[2] * s.z * 0.5).applyMatrix4(anH.fit.matrixWorld).project(camera);
		const x = (tmpV.x * 0.5 + 0.5) * w;
		const y = (-tmpV.y * 0.5 + 0.5) * hgt;
		el.style.transform = `translate3d(${x.toFixed(1)}px, ${y.toFixed(1)}px, 0)`;
		el.classList.toggle('callout--left', x > w * 0.55);
		el.style.opacity = anatomyState.show['a' + i].toFixed(3);
	});
}

/* ------------------------------------------------------------------------
 * Intro (curtain + hero entrance)
 * ---------------------------------------------------------------------- */
function intro() {
	const root = document.documentElement;
	const start = () => {
		root.classList.add('is-intro');
		if (heroH) {
			heroH.st.intro = 0;
			heroH.st.spinIntro = -2.4;
			E.tween(heroH.st, { intro: 1, spinIntro: 0 }, { duration: reduced ? 0 : 2.2, ease: 'expoOut', delay: 0.1 });
		}
	};
	if (loaderEl) {
		loaderEl.classList.add('is-done');
		setTimeout(start, reduced ? 0 : 650);
		setTimeout(() => loaderEl.remove(), 1300);
	} else {
		start();
	}
	if (E.lenis) E.lenis.start();
}

/* ------------------------------------------------------------------------
 * Boot
 * ---------------------------------------------------------------------- */
(async function boot() {
	if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
	if (E.lenis) E.lenis.stop();
	window.scrollTo(0, 0);

	const timeout = new Promise((r) => setTimeout(r, 12000));
	await Promise.race([loadAll(), timeout]);
	progress.fill(1);
	setLoad();
	await new Promise((r) => setTimeout(r, 450));

	const updates = [heroSection(), showcaseSection(), roomsSection(), anatomySection()].filter(Boolean);
	E.onFrame((time, dt) => {
		updates.forEach((u) => u(dt));
		render(time, dt);
	});
	intro();
})();
