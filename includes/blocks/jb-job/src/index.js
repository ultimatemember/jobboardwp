(()=>{"use strict";var e={n:o=>{var t=o&&o.__esModule?()=>o.default:()=>o;return e.d(t,{a:t}),t},d:(o,t)=>{for(var n in t)e.o(t,n)&&!e.o(o,n)&&Object.defineProperty(o,n,{enumerable:!0,get:t[n]})},o:(e,o)=>Object.prototype.hasOwnProperty.call(e,o)};const o=window.wp.data,t=window.wp.components,n=window.wp.blockEditor,r=window.wp.serverSideRender;var i=e.n(r);const s=window.wp.blocks,a=window.ReactJSXRuntime;(0,s.registerBlockType)("jb-block/jb-job",{edit:function(e){const{attributes:r,setAttributes:s}=e,{job_id:d}=r,c=(0,n.useBlockProps)(),l=(0,o.useSelect)((e=>e("core").getEntityRecords("postType","jb-job",{per_page:-1,_fields:["id","title"]})),[]);if(!l)return(0,a.jsxs)("p",{children:[(0,a.jsx)(t.Spinner,{}),wp.i18n.__("Loading...","jobboardwp")]});if(0===l.length)return(0,a.jsx)("p",{children:wp.i18n.__("Jobs not found","jobboardwp")});const b=[{label:"",value:""}].concat(l.map((e=>({label:e.title.rendered,value:e.id}))));return(0,a.jsxs)("div",{...c,children:[(0,a.jsx)(i(),{block:"jb-block/jb-job",attributes:r}),(0,a.jsx)(n.InspectorControls,{children:(0,a.jsx)(t.PanelBody,{title:wp.i18n.__("Job","jobboardwp"),children:(0,a.jsx)(t.SelectControl,{label:wp.i18n.__("Job","jobboardwp"),className:"jb_select_job",value:d,options:b,onChange:e=>s({job_id:e})})})})]})},save:()=>null}),jQuery(window).on("load",(function(e){new MutationObserver((function(e){e.forEach((function(e){jQuery(e.addedNodes).find(".jb-single-job-wrapper").each((function(){const e=document.querySelector(".jb-single-job-wrapper");e&&e.addEventListener("click",(o=>{o.target!==e&&(o.preventDefault(),o.stopPropagation())}))}))}))})).observe(document,{attributes:!1,childList:!0,characterData:!1,subtree:!0})}))})();