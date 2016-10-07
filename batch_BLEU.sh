
FILE_PATH=$1;
FILE_TEMPLATE=$2;

FILES=$FILE_PATH/*
for f in $FILES
do
  if [[ $f == *$FILE_TEMPLATE* ]] ; then
  
	echo $f >> BLEU_gnrl_$FILE_TEMPLATE.txt
	echo -e '\t' >> BLEU_gnrl_$FILE_TEMPLATE.txt
	
	#remove path from model file name
	modelFile=$(echo $f | sed -e "s/\/data\/matiss\/Models\/CharRNN\/done\///g")
	
	#score the translation
	./multi-bleu.perl /mnt/matiss/EXP_2016_10/data/general/general-lv.txt < /mnt/matiss/EXP_2016_10/data/batch/gen.hyb.CharRNN$modelFile.bg.txt | cut -c 1-12 >> BLEU_gnrl_$FILE_TEMPLATE.txt
  
	echo "" >> BLEU_gnrl_$FILE_TEMPLATE.txt
  fi
done
