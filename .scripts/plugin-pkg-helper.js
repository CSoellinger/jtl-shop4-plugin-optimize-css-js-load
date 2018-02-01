const fs = require('fs-extra');
const path = require('path');
const xml2js = require('xml2js');
const del = require('del');
const copy = require('copy');
const semver = require('semver');
const argv = require('yargs').argv;
const shell = require('shelljs');

const xmlParser = new xml2js.Parser();

const nDate = new Date();

const abp = `.${path.sep}`;
const absPathInfoXml = `${abp}info.xml`;
const absPathSrc = `${abp}src`;
const absPathVersion = `${abp}version`;
const absPathDotFiles = `${abp}.files`;
const absPathChangelog = `${abp}CHANGELOG.md`;
const absPathLicense = `${abp}LICENSE`;
const absPathPreview = `${abp}preview.png`;
const absPathReadme = `${abp}README.md`;
const absPathSettings = `${abp}SETTINGS.md`;

const zipFilesArr = [
  absPathDotFiles, absPathVersion, absPathChangelog, absPathInfoXml, absPathLicense, absPathPreview, absPathReadme, absPathSettings
];

const xmlText = fs.readFileSync(path.resolve(absPathInfoXml));

function getLastVersionNr() {
  const regex = /(\<[Vv]ersion[\ a-zA-Z\=\"\'0-9]*\>[\s]*[\<\>\/\-A-Za-z0-9\_]*[\s]*\<\/[Vv]ersion\>)/g;
  const versionArr = xmlText.toString().split(regex);
  versionArr.pop();
  versionArr.shift();
  const lastVersionXmlStr = versionArr[versionArr.length - 1];

  const regexNr = /nr\=\"([0-9]*)\"/g;

  let m;

  while ((m = regexNr.exec(lastVersionXmlStr)) !== null) {
    // This is necessary to avoid infinite loops with zero-width matches
    if (m.index === regexNr.lastIndex) {
      regexNr.lastIndex++;
    }

    return parseInt(m[1], 10);
  }
}

function addNewXmlVersion(versionStep) {
  if (versionStep === true || (versionStep !== 'patch' && versionStep !== 'minor' && versionStep !== 'major')) {
    versionStep = 'patch';
  }

  const regex = /(\<[Vv]ersion[\ a-zA-Z\=\"\'0-9]*\>[\s]*[\<\>\/\-A-Za-z0-9\_]*[\s]*\<\/[Vv]ersion\>)/g;
  const versionArr = xmlText.toString().split(regex);
  versionArr.pop();
  versionArr.shift();
  const lastVersionXmlStr = versionArr[versionArr.length - 1];

  const lastVersionNr = getLastVersionNr();
  const lastVersionNrArr = String(lastVersionNr).split('');

  const patchNr = lastVersionNrArr[lastVersionNrArr.length - 1];
  lastVersionNrArr.pop();

  const minorNr = lastVersionNrArr[lastVersionNrArr.length - 1];
  lastVersionNrArr.pop();

  const majorNr = lastVersionNrArr.join('');

  const semVerNr = `${majorNr}.${minorNr}.${patchNr}`;
  const nextSemVerNr = semver.inc(semVerNr, versionStep);

  const actualDate = `${nDate.getFullYear()}-${nDate.getMonth() <= 9 ? '0' + (nDate.getMonth() + 1) : (nDate.getMonth() + 1)}-${nDate.getDate() <= 9 ? '0' + nDate.getDate() : nDate.getDate()}`;

  const nextVerXmlStr = xmlText.toString().split(/([\ ]*)\<Version/)[1] + lastVersionXmlStr
    .replace(/(nr\=\")([0-9]*)(\")/, `$1${nextSemVerNr.replace(/\./g, '')}$3`)
    .replace(/(\<[Cc]reate[Dd]ate\>)([0-9\-\.\/]*)(\<\/[Cc]reate[Dd]ate\>)/, `$1${actualDate}$3`);

  const newXmlText = xmlText.toString().replace(lastVersionXmlStr, lastVersionXmlStr + "\n" + nextVerXmlStr);

  fs.writeFile(path.resolve(absPathInfoXml), newXmlText, (err) => {
    if (err) {
      console.error(err);
      return;
    }
  });

  fs.copy(path.resolve(absPathSrc), path.resolve(absPathVersion, nextSemVerNr.replace(/\./g, '')));
}

function deleteLastVersion() {
  const regex = /(\<[Vv]ersion[\ a-zA-Z\=\"\'0-9]*\>[\s]*[\<\>\/\-A-Za-z0-9\_]*[\s]*\<\/[Vv]ersion\>)/g;
  const versionArr = xmlText.toString().split(regex);
  versionArr.pop();
  versionArr.shift();
  const lastVersionXmlStr = versionArr[versionArr.length - 1];
  const newXmlText = xmlText.toString().replace(lastVersionXmlStr, '');

  fs.writeFile(path.resolve(absPathInfoXml), newXmlText, (err) => {
    if (err) {
      console.error(err);
      return;
    }
  });

  del(path.resolve(absPathVersion, String(getLastVersionNr())));
}

function zipPkg() {
  xmlParser.parseString(fs.readFileSync(absPathInfoXml), async (err, xmlData) => {
    const pkgName = xmlData.jtlshop3plugin.PluginID[0];
    const absPathZipDir = `${abp}${pkgName}`;
    const pathZipDir = path.resolve(absPathZipDir);

    if (fs.existsSync(pathZipDir)) {
      await del([pathZipDir]);
    }

    fs.mkdirpSync(pathZipDir);

    const promiseCopyArr = [];

    zipFilesArr.forEach((file) => {
      const pathFile = path.resolve(file);
      if (fs.pathExistsSync(pathFile)) {
        if (fs.lstatSync(pathFile).isDirectory()) {
          promiseCopyArr.push(fs.copy(file, `${absPathZipDir}${path.sep}${path.basename(file)}`));
        }
        if (fs.lstatSync(file).isFile()) {
          promiseCopyArr.push(copy(file, `${absPathZipDir}${path.sep}.`, (err) => { if (err) { console.error(err); } }));
        }
      }
    });

    Promise.all(promiseCopyArr).then(async (res) => {
      const versionArr = xmlData.jtlshop3plugin.Install[0].Version;
      const lastVersion = versionArr[versionArr.length - 1];
      const lastVersionNr = parseInt(lastVersion.$.nr, 10);
      const lastVersionNrArr = String(lastVersionNr).split('');

      const pathNr = lastVersionNrArr[lastVersionNrArr.length - 1];
      lastVersionNrArr.pop();

      const minorNr = lastVersionNrArr[lastVersionNrArr.length - 1];
      lastVersionNrArr.pop();

      const majorNr = lastVersionNrArr.join('');

      const semVerNr = `${majorNr}.${minorNr}.${pathNr}`;

      const zipFile = `${abp}${pkgName}_${semVerNr}.zip`;

      if (fs.existsSync(path.resolve(zipFile))) {
        await del([path.resolve(zipFile)]);
      }

      shell.exec(`zip -q -r ${zipFile} ${absPathZipDir}`);

      del([pathZipDir]);
    }).catch((err) => { console.error(err); });
  });
}

for (const key in argv) {
  if (argv.hasOwnProperty(key) && (key.substr(-8) === '-version' || key.substr(-4) === '-pkg')) {
    // Version command
    const element = argv[key];


    if (element) {
      switch (key) {
        case 'create-version':
          addNewXmlVersion(element);
          break;
        case 'delete-version':
          deleteLastVersion();
          break;
        case 'zip-version':
        case 'zip-pkg':
          zipPkg();
          break;
        default:
          break;
      }
      break;
    }
  }
}
